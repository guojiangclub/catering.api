<?php

namespace GuoJiangClub\Catering\Server\Channels;

use Carbon\Carbon;
use GuoJiangClub\Catering\Component\Order\Models\Adjustment;
use GuoJiangClub\Catering\Backend\Models\Clerk;
use GuoJiangClub\Catering\Backend\Models\Order;
use GuoJiangClub\Catering\Backend\Models\Payment;
use GuoJiangClub\Catering\Backend\Models\Point;
use GuoJiangClub\Catering\Backend\Models\Refund;
use GuoJiangClub\Catering\Server\Contracts\UnifiedOrderContracts;
use GuoJiangClub\Catering\Server\Applicator\PointApplicator;
use GuoJiangClub\Catering\Component\Point\Repository\PointRepository;
use GuoJiangClub\Catering\Component\Balance\Repository\BalanceRepository;
use DB;

class BalancePointChannel extends BaseChannel implements UnifiedOrderContracts
{
	const TYPE = 'balance_point';

	protected $balanceRepository;
	protected $pointRepository;
	protected $pointApplicator;

	public function __construct(BalanceRepository $balanceRepository, PointRepository $pointRepository, PointApplicator $pointApplicator)
	{
		$this->balanceRepository = $balanceRepository;
		$this->pointRepository   = $pointRepository;
		$this->pointApplicator   = $pointApplicator;
	}

	public function unifiedOrder(array $params, $user): array
	{
		try {

			$balance = $this->balanceRepository->getSum($user->id);
			if ($balance <= 0 || $balance < $params['balance']) {
				throw new \Exception('余额不足');
			}

			$pointValid = $this->pointRepository->getSumPointValid($user->id);
			if ($pointValid <= 0 || $pointValid < $params['point']) {
				throw new \Exception('积分不足');
			}

			$money = (int) round($params['point'] * settings('point_deduction_money'), 0, PHP_ROUND_HALF_DOWN);
			if ($money != $params['point_money'] || ($params['point_money'] + $params['balance']) != $params['total_amount']) {
				throw new \Exception('积分抵扣信息错误');
			}

			DB::beginTransaction();

			$order = new Order([
				'user_id'      => $user->id,
				'order_no'     => build_order_no('BST'),
				'count'        => 1,
				'items_total'  => $params['total_amount'],
				'redeem_point' => $params['point'],
				'channel'      => 'st',
				'status'       => Order::STATUS_TEMP,
				'submit_time'  => Carbon::now(),
				'type'         => Order::TYPE_BALANCE_AND_POINT,
				'note'         => !empty($params['note']) ? $params['note'] : null,
			]);

			$applicator = $this->pointApplicator->apply($order, $params['point']);
			if (!$applicator) {
				throw new \Exception('积分处理错误');
			}

			$order->total = $order->total - $order->getPaidAmount();
			if ($order->total != $params['balance'] || abs($order->adjustments_total) != $params['point_money']) {
				throw new \Exception('积分处理错误');
			}

			$order->save();

			$this->pointRepository->create([
				'user_id'    => $user->id,
				'action'     => Point::ACTION_ORDER_USED,
				'note'       => '积分使用订单：' . $params['point'] . ',订单号：' . $order->order_no,
				'value'      => (-1) * $params['point'],
				'valid_time' => 0,
				'item_type'  => Order::class,
				'item_id'    => $order->id,
			]);

			event('point.change', $user->id);

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

			DB::commit();

			return ['status' => true, 'data' => ['order_no' => $order->order_no, 'type' => Payment::TYPE_MIXED]];
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
			$payment = $order->payments()->where('channel', Payment::TYPE_BALANCE)->where('status', Payment::STATUS_COMPLETED)->first();
			if (!$payment) {
				throw new \Exception('余额抵扣错误，无法退款');
			}

			$balance = $this->balanceRepository->findWhere(['user_id' => $order->user_id, 'type' => 'order_payment', 'origin_id' => $payment->id, 'origin_type' => Payment::class])->first();
			if (!$balance) {
				throw new \Exception('余额抵扣金额错误，无法退款');
			}

			$adjustment = $order->adjustments()->where('type', Adjustment::ORDER_POINT_DISCOUNT_ADJUSTMENT)->where('origin_type', 'point')->where('origin_id', $order->user_id)->first();
			if (!$adjustment || $adjustment->amount != $order->adjustments_total) {
				throw new \Exception('积分抵扣金额错误，无法退款');
			}

			$point = $this->pointRepository->findWhere(['user_id' => $order->user_id, 'action' => Point::ACTION_ORDER_USED, 'item_id' => $order->id, 'item_type' => Order::class])->first();
			if (!$point || abs($point->value) != $order->redeem_point) {
				throw new \Exception('积分抵扣信息错误，无法退款');
			}

			if (($payment->amount + abs($adjustment->amount)) != $order->items_total) {
				throw new \Exception('优惠抵扣金额，无法退款');
			}

			DB::beginTransaction();

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

			$current_balance = $this->balanceRepository->getSum($order->user_id);

			$this->balanceRepository->create([
				'user_id'         => $order->user_id,
				'type'            => 'order_refund',
				'note'            => '订单退款：' . $payment->amount / 100 . ',订单号：' . $order->order_no,
				'value'           => $payment->amount,
				'current_balance' => $current_balance + $payment->amount,
				'origin_id'       => $payment->id,
				'origin_type'     => Payment::class,
			]);

			$refund = Refund::create([
				'order_id'            => $order->id,
				'clerk_id'            => $clerk->id,
				'user_id'             => $order->user_id,
				'refund_type'         => Payment::TYPE_MIXED,
				'refund_no'           => build_order_no('STR'),
				'refundTargetOrderId' => '',
				'refund_amount'       => 0,
				'refundFundsDesc'     => '订单退款,退还积分：' . $order->redeem_point . '退还余额：' . ($payment->amount / 100) . ',订单号：' . $order->order_no,
				'targetSys'           => '',
				'bankInfo'            => '',
			]);

			$order->status = Order::STATUS_REFUND;
			$order->save();

			DB::commit();

			return ['status' => true, 'data' => ['refund' => $refund, 'type' => Payment::TYPE_MIXED]];
		} catch (\Exception $exception) {
			DB::rollBack();

			\Log::info($exception->getMessage());
			\Log::info($exception->getTraceAsString());

			return ['status' => false, 'message' => $exception->getMessage()];
		}
	}

	public function checkout($order)
	{
		$payment    = $order->payments()->where('channel', Payment::TYPE_BALANCE)->first();
		$adjustment = $order->adjustments()->where('type', Adjustment::ORDER_POINT_DISCOUNT_ADJUSTMENT)->first();
		if ($payment && $adjustment && $payment->amount == $order->total && $adjustment->amount == $order->adjustments_total) {
			$order->status     = Order::STATUS_PAY;
			$order->pay_status = 1;
			$order->pay_time   = Carbon::now();

			$order->save();

			event('st.on.balance.changed', [$order, request('formId')]);
		}
	}
}