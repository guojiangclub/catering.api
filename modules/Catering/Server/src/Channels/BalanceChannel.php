<?php

namespace GuoJiangClub\Catering\Server\Channels;

use Carbon\Carbon;
use GuoJiangClub\Catering\Backend\Models\Clerk;
use GuoJiangClub\Catering\Backend\Models\Order;
use GuoJiangClub\Catering\Backend\Models\Refund;
use GuoJiangClub\Catering\Server\Contracts\UnifiedOrderContracts;
use GuoJiangClub\Catering\Component\Balance\Repository\BalanceRepository;
use GuoJiangClub\Catering\Backend\Models\Payment;
use DB;

class BalanceChannel extends BaseChannel implements UnifiedOrderContracts
{
	const TYPE = 'balance';

	protected $balanceRepository;

	public function __construct(BalanceRepository $balanceRepository)
	{
		$this->balanceRepository = $balanceRepository;
	}

	public function unifiedOrder(array $params, $user): array
	{
		try {

			$balance = $this->balanceRepository->getSum($user->id);
			if ($balance <= 0 || $balance < $params['balance'] || $balance < $params['total_amount']) {
				throw new \Exception('余额不足');
			}

			DB::beginTransaction();

			$order   = Order::create([
				'user_id'     => $user->id,
				'order_no'    => build_order_no('ST'),
				'count'       => 1,
				'items_total' => $params['total_amount'],
				'total'       => $params['total_amount'],
				'channel'     => 'st',
				'status'      => Order::STATUS_TEMP,
				'submit_time' => Carbon::now(),
				'type'        => Order::TYPE_BALANCE,
				'note'        => !empty($params['note']) ? $params['note'] : null,
			]);
			$payment = new Payment([
				'order_id' => $order->id,
				'channel'  => Payment::TYPE_BALANCE,
				'amount'   => $params['total_amount'],
				'status'   => Payment::STATUS_COMPLETED,
				'paid_at'  => Carbon::now(),
			]);
			$order->payments()->save($payment);

			$current_balance = $balance - $params['total_amount'];
			if ($current_balance <= 0) {
				$current_balance = 0;
			}

			$this->balanceRepository->create([
				'user_id'         => $user->id,
				'type'            => 'order_payment',
				'note'            => '订单余额支付：' . $params['total_amount'] / 100 . ',订单号：' . $order->order_no,
				'value'           => -$params['total_amount'],
				'current_balance' => $current_balance,
				'origin_id'       => $payment->id,
				'origin_type'     => Payment::class,
			]);

			DB::commit();

			return ['status' => true, 'data' => ['order_no' => $order->order_no, 'type' => Payment::TYPE_BALANCE]];
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
			if (!$payment || $payment->amount != $order->total) {
				throw new \Exception('余额抵扣错误，无法退款');
			}

			$balance = $this->balanceRepository->findWhere(['user_id' => $order->user_id, 'type' => 'order_payment', 'origin_id' => $payment->id, 'origin_type' => Payment::class])->first();
			if (!$balance || abs($balance->value) != $order->total) {
				throw new \Exception('余额抵扣金额错误，无法退款');
			}

			DB::beginTransaction();

			$current_balance = $this->balanceRepository->getSum($order->user_id);

			$this->balanceRepository->create([
				'user_id'         => $order->user_id,
				'type'            => 'order_refund',
				'note'            => '订单退款退还余额：' . $order->total / 100 . ',订单号：' . $order->order_no,
				'value'           => $order->total,
				'current_balance' => $current_balance + $order->total,
				'origin_id'       => $payment->id,
				'origin_type'     => Payment::class,
			]);

			$refund = Refund::create([
				'order_id'            => $order->id,
				'clerk_id'            => $clerk->id,
				'user_id'             => $order->user_id,
				'refund_type'         => Payment::TYPE_BALANCE,
				'refund_no'           => build_order_no('STR'),
				'refundTargetOrderId' => '',
				'refund_amount'       => $payment->amount,
				'refundFundsDesc'     => '订单退款：' . $payment->amount / 100 . ',订单号：' . $order->order_no,
				'targetSys'           => '',
				'bankInfo'            => '',
			]);

			$order->status = Order::STATUS_REFUND;
			$order->save();

			DB::commit();

			return ['status' => true, 'data' => ['refund' => $refund, 'type' => Payment::TYPE_BALANCE]];
		} catch (\Exception $exception) {
			DB::rollBack();

			\Log::info($exception->getMessage());
			\Log::info($exception->getTraceAsString());

			return ['status' => false, 'message' => $exception->getMessage()];
		}
	}

	public function checkout($order)
	{
		$payment = $order->payments()->where('channel', Payment::TYPE_BALANCE)->first();
		if ($payment && $payment->amount == $order->total) {
			$order->status     = Order::STATUS_PAY;
			$order->pay_status = 1;
			$order->pay_time   = Carbon::now();

			$order->save();

			event('st.on.balance.changed', [$order, request('formId')]);
		}
	}
}