<?php

namespace GuoJiangClub\Catering\Server\Channels;

use Carbon\Carbon;
use GuoJiangClub\Catering\Component\Order\Models\Adjustment;
use GuoJiangClub\Catering\Backend\Models\Clerk;
use GuoJiangClub\Catering\Backend\Models\Order;
use GuoJiangClub\Catering\Backend\Models\Payment;
use GuoJiangClub\Catering\Backend\Models\Coupon\Coupon;
use GuoJiangClub\Catering\Backend\Models\Point;
use GuoJiangClub\Catering\Backend\Models\Refund;
use GuoJiangClub\Catering\Server\Contracts\UnifiedOrderContracts;
use GuoJiangClub\Catering\Server\Applicator\PointApplicator;
use GuoJiangClub\Catering\Component\Point\Repository\PointRepository;
use GuoJiangClub\Catering\Component\Balance\Repository\BalanceRepository;
use GuoJiangClub\Catering\Component\Discount\Services\DiscountService;
use GuoJiangClub\Catering\Server\Applicator\DiscountApplicator;
use GuoJiangClub\Catering\Component\Payment\Contracts\PaymentChargeContract;
use EasyWeChat;
use DB;
use GuoJiangClub\Catering\Server\Service\UnionPayService;

class WeChatChannel extends BaseChannel implements UnifiedOrderContracts
{
	const TYPE = 'wx_lite';

	protected $balanceRepository;
	protected $pointRepository;
	protected $pointApplicator;
	protected $chargeContract;
	protected $discountService;
	protected $discountApplicator;

	public function __construct(BalanceRepository $balanceRepository, PointRepository $pointRepository, PointApplicator $pointApplicator, PaymentChargeContract $chargeContract, DiscountService $discountService, DiscountApplicator $discountApplicator)
	{
		$this->balanceRepository  = $balanceRepository;
		$this->pointRepository    = $pointRepository;
		$this->pointApplicator    = $pointApplicator;
		$this->chargeContract     = $chargeContract;
		$this->discountService    = $discountService;
		$this->discountApplicator = $discountApplicator;
	}

	public function unifiedOrder(array $params, $user): array
	{
		try {

			$miniProgram = EasyWeChat::miniProgram('shitang');
			$result      = $miniProgram->auth->session($params['code']);
			if (!isset($result['openid'])) {
				throw new \Exception('获取openid失败');
			}

			if ($params['amount'] <= 0) {
				throw new \Exception('支付金额错误');
			}

			DB::beginTransaction();

			$payConfig       = settings('shitang_miniProgram_pay_config');
			$order_no        = $payConfig['msgSrcId'] . date('YymdHis') . mt_rand(10000000, 99999999);
			$order           = Order::create([
				'user_id'     => $user->id,
				'items_total' => $params['total_amount'],
				'total'       => $params['total_amount'],
				'count'       => 1,
				'channel'     => 'st',
				'note'        => !empty($params['note']) ? $params['note'] : null,
			]);
			$order->order_no = $order_no;

			if ($params['balance']) {
				$balance = $this->balanceRepository->getSum($user->id);
				if ($balance <= 0 || $balance < $params['balance']) {
					throw new \Exception('余额不足');
				}

				$payment = new Payment([
					'order_id' => $order->id,
					'channel'  => Payment::TYPE_BALANCE,
					'amount'   => $params['balance'],
					'status'   => Payment::STATUS_COMPLETED,
					'paid_at'  => Carbon::now(),
				]);
				$order->payments()->save($payment);

				$current_balance = $balance - $params['balance'];
				if ($current_balance <= 0) {
					$current_balance = 0;
				}

				$this->balanceRepository->addRecord([
					'user_id'         => $user->id,
					'type'            => 'order_payment',
					'note'            => '订单余额支付：' . $params['balance'] / 100 . ',订单号：' . $order->order_no,
					'value'           => -$params['balance'],
					'current_balance' => $current_balance,
					'origin_id'       => $payment->id,
					'origin_type'     => Payment::class,
				]);
			}

			if ($params['point'] && $params['point_money']) {
				$pointValid = $this->pointRepository->getSumPointValid($user->id);
				if ($pointValid <= 0 || $pointValid < $params['point']) {
					throw new \Exception('积分不足');
				}

				$applicator = $this->pointApplicator->apply($order, $params['point']);
				if (!$applicator) {
					throw new \Exception('积分处理错误');
				}

				$this->pointRepository->create([
					'user_id'    => $user->id,
					'action'     => 'order_point',
					'note'       => '积分订单：' . $params['point'] . ',订单号：' . $order->order_no,
					'value'      => (-1) * $params['point'],
					'valid_time' => 0,
					'item_type'  => Order::class,
					'item_id'    => $order->id,
				]);

				$order->redeem_point = $params['point'];

				event('point.change', $user->id);

				$order->save();
			}

			if ($params['coupon_id']) {
				$coupon = Coupon::find($params['coupon_id']);
				if (!empty($coupon)) {
					if ($coupon->used_at != null) {
						throw new \Exception('优惠券已被使用');
					}

					$order->recalculateTotal();
					if (!$user->can('update', $coupon) || !$this->discountService->checkCoupon($order, $coupon)) {
						throw new \Exception('优惠券信息有误，请确认后重试');
					}

					$this->discountApplicator->apply($order, $coupon);

					$order->save();
				}
			}

			$order->recalculateTotal();
			if ($order->getNeedPayAmount() != $params['amount']) {
				throw new \Exception('支付金额错误');
			}

			$order->save();

			$charge = $this->chargeContract->createCharge($user->id, 'wx_lite', 'order', $order->order_no, $order->getNeedPayAmount(), '', '', request()->getClientIp(), $result['openid'], '');
			if (empty($charge)) {
				throw new \Exception('请求支付失败');
			}

			DB::commit();

			return ['status' => true, 'data' => $charge];
		} catch (\Exception $exception) {
			DB::rollBack();

			\Log::info($exception->getMessage());
			\Log::info($exception->getTraceAsString());

			return ['status' => false, 'message' => $exception->getMessage()];
		}
	}

	public function refund($order, Clerk $clerk): array
	{
		try {
			$balance = null;
			$point   = null;
			$coupon  = null;

			$balance_payment = $order->payments()->where('channel', Payment::TYPE_BALANCE)->where('status', Payment::STATUS_COMPLETED)->first();
			if ($balance_payment) {
				$balance = $this->balanceRepository->findWhere(['user_id' => $order->user_id, 'type' => 'order_payment', 'origin_id' => $balance_payment->id, 'origin_type' => Payment::class])->first();
				if (!$balance || abs($balance->value) != $balance_payment->amount) {
					throw new \Exception('余额抵扣金额错误，无法退款');
				}
			}

			$point_adjustment        = $order->adjustments()->where('origin_type', 'point')->where('type', Adjustment::ORDER_POINT_DISCOUNT_ADJUSTMENT)->first();
			$point_adjustment_amount = 0;
			if ($point_adjustment) {
				$point = $this->pointRepository->findWhere(['user_id' => $order->user_id, 'action' => 'order_point', 'item_type' => Order::class, 'item_id' => $order->id])->first();
				if (!$point) {
					throw new \Exception('积分抵扣信息错误，无法退款');
				}

				$point_adjustment_amount = $point_adjustment->amount;
			}

			$coupon_adjustment        = $order->adjustments()->where('origin_type', 'coupon')->where('type', Adjustment::ORDER_DISCOUNT_ADJUSTMENT)->first();
			$coupon_adjustment_amount = 0;
			if ($coupon_adjustment) {
				$coupon = Coupon::where('id', $coupon_adjustment->origin_id)->whereNotNull('used_at')->first();
				if (!$coupon) {
					throw new \Exception('优惠券信息错误，无法退款');
				}

				$coupon_adjustment_amount = $coupon_adjustment->amount;
			}

			if (($point_adjustment_amount + $coupon_adjustment_amount) != $order->adjustments_total) {
				throw new \Exception('优惠金额错误，无法退款');
			}

			$wx_payment = $order->payments()->where('channel', Payment::TYPE_WX_LITE)->where('status', Payment::STATUS_COMPLETED)->first();
			if (!$wx_payment) {
				throw new \Exception('支付信息错误，无法退款');
			}

			if ($balance && ($wx_payment->amount + $balance_payment->amount) != $order->total) {
				throw new \Exception('支付金额信息错误，无法退款');
			}

			if (is_null($balance) && $wx_payment->amount != $order->total) {
				throw new \Exception('支付金额信息错误，无法退款');
			}

			DB::beginTransaction();

			if ($balance) {
				$current_balance = $this->balanceRepository->getSum($order->user_id);

				$this->balanceRepository->create([
					'user_id'         => $order->user_id,
					'type'            => 'order_refund',
					'note'            => '订单退款：' . $balance_payment->amount / 100 . ',订单号：' . $order->order_no,
					'value'           => $balance_payment->amount,
					'current_balance' => $current_balance + $balance_payment->amount,
					'origin_id'       => $balance_payment->id,
					'origin_type'     => Payment::class,
				]);
			}

			if ($point) {
				$this->pointRepository->create([
					'user_id'    => $order->user_id,
					'action'     => Point::ACTION_ORDER_REFUND,
					'note'       => '订单退款：' . $order->redeem_point . ',订单号：' . $order->order_no,
					'value'      => $order->redeem_point,
					'valid_time' => 0,
					'item_type'  => Order::class,
					'item_id'    => $order->id,
				]);

				event('point.change', $order->user_id);
			}

			if ($coupon) {
				$coupon->used_at = null;
				$coupon->save();
			}

			$result = UnionPayService::orderRefund($order->order_no, $wx_payment->amount);
			if (!$result) {
				throw new \Exception('退款失败');
			}

			$refund = Refund::create([
				'order_id'            => $order->id,
				'clerk_id'            => $clerk->id,
				'user_id'             => $order->user_id,
				'refund_type'         => Payment::TYPE_WX_LITE,
				'refund_no'           => isset($result['refundOrderId']) ? $result['refundOrderId'] : '',
				'refundTargetOrderId' => isset($result['refundTargetOrderId']) ? $result['refundTargetOrderId'] : '',
				'refund_amount'       => $wx_payment->amount,
				'refundFundsDesc'     => isset($result['refundFundsDesc']) ? $result['refundFundsDesc'] : '',
				'targetSys'           => isset($result['targetSys']) ? $result['targetSys'] : '',
				'bankInfo'            => isset($result['bankInfo']) ? $result['bankInfo'] : '',
			]);

			$order->status = Order::STATUS_REFUND;
			$order->save();

			DB::commit();

			return ['status' => true, 'data' => ['refund' => $refund, 'type' => Payment::TYPE_WX_LITE]];
		} catch (\Exception $exception) {
			DB::rollBack();

			\Log::info($exception->getMessage());
			\Log::info($exception->getTraceAsString());

			return ['status' => false, 'message' => $exception->getMessage()];
		}
	}

	public function checkout($order)
	{

	}
}