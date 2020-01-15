<?php

namespace GuoJiangClub\Catering\Server\Channels;

use Carbon\Carbon;
use GuoJiangClub\Catering\Component\Order\Models\Adjustment;
use GuoJiangClub\Catering\Backend\Models\Clerk;
use GuoJiangClub\Catering\Backend\Models\Order;
use GuoJiangClub\Catering\Backend\Models\Payment;
use GuoJiangClub\Catering\Backend\Models\Point;
use GuoJiangClub\Catering\Backend\Models\Refund;
use GuoJiangClub\Catering\Server\Applicator\PointApplicator;
use GuoJiangClub\Catering\Component\Point\Repository\PointRepository;
use GuoJiangClub\Catering\Server\Contracts\UnifiedOrderContracts;
use DB;

class PointChannel extends BaseChannel implements UnifiedOrderContracts
{
	const TYPE = 'point';

	protected $pointRepository;
	protected $pointApplicator;

	public function __construct(PointRepository $pointRepository, PointApplicator $pointApplicator)
	{
		$this->pointRepository = $pointRepository;
		$this->pointApplicator = $pointApplicator;
	}

	public function unifiedOrder(array $params, $user): array
	{
		try {

			$pointValid = $this->pointRepository->getSumPointValid($user->id);
			if ($pointValid < $params['point']) {
				throw new \Exception('积分不足');
			}

			$money = (int) round($params['point'] * settings('point_deduction_money'), 0, PHP_ROUND_HALF_DOWN);
			if ($money != $params['point_money'] || $params['point_money'] != $params['total_amount']) {
				throw new \Exception('积分抵扣信息错误');
			}

			DB::beginTransaction();

			$order = new Order([
				'user_id'      => $user->id,
				'order_no'     => build_order_no('ST'),
				'count'        => 1,
				'items_total'  => $params['total_amount'],
				'redeem_point' => $params['point'],
				'channel'      => 'st',
				'status'       => Order::STATUS_TEMP,
				'submit_time'  => Carbon::now(),
				'type'         => Order::TYPE_ALL_POINT,
				'note'         => !empty($params['note']) ? $params['note'] : null,
			]);

			$applicator = $this->pointApplicator->apply($order, $params['point']);
			if (!$applicator) {
				throw new \Exception('积分处理错误');
			}

			if ($order->total != 0 || abs($order->adjustments_total) != $params['total_amount']) {
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

			DB::commit();

			return ['status' => true, 'data' => ['order_no' => $order->order_no, 'type' => Payment::TYPE_POINT]];
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
			$adjustment = $order->adjustments()->where('type', Adjustment::ORDER_POINT_DISCOUNT_ADJUSTMENT)->where('origin_type', 'point')->where('origin_id', $order->user_id)->first();
			if (!$adjustment || $adjustment->amount != $order->adjustments_total) {
				throw new \Exception('积分抵扣金额错误，无法退款');
			}

			$point = $this->pointRepository->findWhere(['user_id' => $order->user_id, 'action' => Point::ACTION_ORDER_USED, 'item_id' => $order->id, 'item_type' => Order::class])->first();
			if (!$point || abs($point->value) != $order->redeem_point) {
				throw new \Exception('积分抵扣信息错误，无法退款');
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

			$refund = Refund::create([
				'order_id'            => $order->id,
				'clerk_id'            => $clerk->id,
				'user_id'             => $order->user_id,
				'refund_type'         => Payment::TYPE_POINT,
				'refund_no'           => build_order_no('STR'),
				'refundTargetOrderId' => '',
				'refund_amount'       => 0,
				'refundFundsDesc'     => '订单退款退还积分：' . $order->redeem_point . ',订单号：' . $order->order_no,
				'targetSys'           => '',
				'bankInfo'            => '',
			]);

			$order->status = Order::STATUS_REFUND;
			$order->save();

			DB::commit();

			return ['status' => true, 'data' => ['refund' => $refund, 'type' => Payment::TYPE_POINT]];
		} catch (\Exception $exception) {
			DB::rollBack();

			\Log::info($exception->getMessage());
			\Log::info($exception->getTraceAsString());

			return ['status' => false, 'message' => $exception->getMessage()];
		}
	}

	public function checkout($order)
	{
		$adjustment = $order->adjustments()->where('type', Adjustment::ORDER_POINT_DISCOUNT_ADJUSTMENT)->first();
		if ($adjustment && $adjustment->amount == $order->adjustments_total && abs($adjustment->amount) == $order->items_total) {
			$order->status     = Order::STATUS_PAY;
			$order->pay_status = 1;
			$order->pay_time   = Carbon::now();

			$order->save();

			event('st.on.point.changed', [$order, request('formId')]);
		}
	}
}