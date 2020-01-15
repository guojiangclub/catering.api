<?php

namespace GuoJiangClub\Catering\Server\Http\Controllers;

use Carbon\Carbon;
use GuoJiangClub\Catering\Backend\Models\Clerk;
use GuoJiangClub\Catering\Backend\Models\Coupon\Coupon;
use GuoJiangClub\Catering\Backend\Models\Order;
use GuoJiangClub\Catering\Backend\Models\Payment;
use GuoJiangClub\Catering\Server\Channels\BalanceChannel;
use GuoJiangClub\Catering\Server\Channels\WeChatChannel;
use GuoJiangClub\Catering\Server\Repositories\BalanceOrderRepository;
use GuoJiangClub\Catering\Server\Repositories\OrderRepository;
use GuoJiangClub\Catering\Server\Transformers\BalanceOrderTransformer;
use GuoJiangClub\Catering\Server\Transformers\CouponsTransformer;
use GuoJiangClub\Catering\Server\Transformers\OrderTransformer;

class CommercialController extends Controller
{
	protected $orderRepository;
	protected $balanceOrderRepository;

	public function __construct(OrderRepository $orderRepository, BalanceOrderRepository $balanceOrderRepository)
	{
		$this->orderRepository        = $orderRepository;
		$this->balanceOrderRepository = $balanceOrderRepository;
	}

	public function detail()
	{
		$conditions      = $this->createConditions();
		$where           = $conditions['where'];
		$time            = $conditions['time'];
		$where['status'] = ['>', 0];

		$orders = $this->orderRepository->getOrdersByCondition($where, $time, 0);
		$result = $this->calculate($orders);

		$balance_amount       = 0;
		$total_balance_orders = 0;
		unset($where['status']);
		$where['pay_status'] = 1;
		$balance_orders      = $this->balanceOrderRepository->getOrdersByCondition($where, $time, 0);
		if (count($balance_orders) > 0) {
			$balance_amount       = $balance_orders->sum('pay_amount');
			$total_balance_orders = $balance_orders->count();
		}

		$result['total_balance_orders'] = $total_balance_orders;
		$result['balance_amount']       = number_format($balance_amount / 100, 2, '.', '');
		$result['actual_amount']        = number_format(($result['balance_amount'] + $result['total'] - $result['refund_total']), 2, '.', '');

		return $this->success($result);
	}

	public function balanceDetail()
	{
		$conditions      = $this->createConditions();
		$where           = $conditions['where'];
		$time            = $conditions['time'];
		$where['status'] = Order::STATUS_PAY;

		$used_balance_amount = 0;
		$used_balance_orders = 0;
		$orders              = $this->orderRepository->getOrdersByCondition($where, $time, 0);
		if (count($orders) > 0) {
			foreach ($orders as $order) {
				if ($order->status == Order::STATUS_PAY AND $payment = $order->payments()->where('channel', BalanceChannel::TYPE)->where('status', Payment::STATUS_COMPLETED)->first()) {
					$used_balance_amount += $payment->amount;
					$used_balance_orders++;
				}
			}
		}

		$total_balance_orders    = 0;
		$total_balance_amount    = 0;
		$balance_amount          = 0;
		$discount_balance_amount = 0;
		unset($where['status']);
		$where['pay_status'] = 1;
		$balance_orders      = $this->balanceOrderRepository->getOrdersByCondition($where, $time, 0);
		if (count($balance_orders) > 0) {
			$total_balance_orders    = $balance_orders->count();
			$total_balance_amount    = $balance_orders->sum('amount');
			$balance_amount          = $balance_orders->sum('pay_amount');
			$discount_balance_amount = $total_balance_amount - $balance_amount;
		}

		return $this->success([
			'used_balance_amount'     => number_format($used_balance_amount / 100, 2, '.', ''),
			'used_balance_orders'     => $used_balance_orders,
			'total_balance_orders'    => $total_balance_orders,
			'total_balance_amount'    => number_format($total_balance_amount / 100, 2, '.', ''),
			'balance_amount'          => number_format($balance_amount / 100, 2, '.', ''),
			'discount_balance_amount' => number_format($discount_balance_amount / 100, 2, '.', ''),
		]);
	}

	public function orderList()
	{
		$limit           = request('limit') ?: 15;
		$conditions      = $this->createConditions();
		$where           = $conditions['where'];
		$time            = $conditions['time'];
		$where['status'] = ['>', 0];

		$list = $this->orderRepository->getOrdersByCondition($where, $time, $limit, ['user']);

		return $this->response()->paginator($list, new OrderTransformer());
	}

	public function balanceOrderList()
	{
		$limit               = request('limit') ?: 15;
		$conditions          = $this->createConditions();
		$where               = $conditions['where'];
		$time                = $conditions['time'];
		$where['pay_status'] = 1;

		$list = $this->balanceOrderRepository->getOrdersByCondition($where, $time, $limit, ['user']);

		return $this->response()->paginator($list, new BalanceOrderTransformer());
	}

	public function orderDetail($order_no)
	{
		$order = $this->orderRepository->getOrderByNo($order_no);
		if (!$order) {
			return $this->failed('订单不存在');
		}

		return $this->success($order);
	}

	public function balanceUsedOrderList()
	{
		$limit            = request('limit') ?: 15;
		$conditions       = $this->createConditions();
		$where            = $conditions['where'];
		$time             = $conditions['time'];
		$where['status']  = Order::STATUS_PAY;
		$where['balance'] = 1;

		$list = $this->orderRepository->getOrdersByCondition($where, $time, $limit, ['user']);

		return $this->response()->paginator($list, new OrderTransformer());
	}

	public function balanceOrderDetail($order_no)
	{
		$order = $this->balanceOrderRepository->getOrderByNo($order_no);
		if (!$order) {
			return $this->failed('订单不存在');
		}

		$order->amount_yuan     = number_format($order->amount / 100, 2, '.', '');
		$order->pay_amount_yuan = number_format($order->pay_amount / 100, 2, '.', '');

		return $this->success($order);
	}

	public function createConditions()
	{
		$time  = [];
		$where = [];

		$type  = request('type') ?: 'all';
		$start = request('start_time');
		$end   = request('end_time');
		if ($type == 'day') {
			$start_time = $start . ' 00:00:00';
			$end_time   = $start . ' 23:59:59';
		} elseif ($type == 'week') {
			$start_time = $start . ' 00:00:00';
			$end_time   = $end . ' 23:59:59';
		} elseif ($type == 'month') {
			$start_time = $start . '-01 00:00:00';
			$end_time   = $start . '-' . date('t', strtotime($start)) . ' 23:59:59';
		}

		if ($type != 'all') {
			$where['created_at'] = ['>=', $start_time];
			$time['created_at']  = ['<=', $end_time];
		}

		if (request('order_no')) {
			$where['order_no'] = ['like', '%' . request('order_no') . '%'];
		}

		if (request('mobile')) {
			$where['mobile'] = ['like', '%' . request('mobile') . '%'];
		}

		return ['where' => $where, 'time' => $time];
	}

	public function calculate($orders)
	{
		$items_total       = 0;
		$adjustments_total = 0;
		$total             = 0;
		$balance           = 0;
		$refund_total      = 0;
		$refund_orders     = 0;
		$total_orders      = 0;

		if (count($orders) > 0) {
			$items_total       = $orders->sum('items_total');
			$adjustments_total = abs($orders->sum('adjustments_total'));
			$total_orders      = count($orders);
			$refund_orders     = $orders->where('status', Order::STATUS_REFUND)->count();

			foreach ($orders as $order) {
				$payment = $order->payments()->where('channel', WeChatChannel::TYPE)->where('status', Payment::STATUS_COMPLETED)->first();
				if ($payment) {
					$total += $payment->amount;
				}

				$pay = $order->payments()->where('channel', BalanceChannel::TYPE)->where('status', Payment::STATUS_COMPLETED)->first();
				if ($pay) {
					$balance += $pay->amount;
				}

				if ($order->status == Order::STATUS_REFUND AND $refund_payment = $order->payments()->where('channel', WeChatChannel::TYPE)->where('status', Payment::STATUS_COMPLETED)->first()) {
					$refund_total += $refund_payment->amount;
				}
			}
		}

		return [
			'items_total'       => number_format($items_total / 100, 2, '.', ''),
			'adjustments_total' => number_format($adjustments_total / 100, 2, '.', ''),
			'total'             => number_format($total / 100, 2, '.', ''),
			'balance'           => number_format($balance / 100, 2, '.', ''),
			'refund_total'      => number_format($refund_total / 100, 2, '.', ''),
			'refund_orders'     => $refund_orders,
			'total_orders'      => $total_orders,
		];
	}

	public function refund($order_no)
	{
		$clerk = auth('shitang')->user();
		if (!$clerk instanceof Clerk || !$clerk->is_clerk_owner) {
			return $this->failed('没有权限操作');
		}

		$order = Order::where('order_no', $order_no)->where('status', Order::STATUS_PAY)->whereIn('type', [Order::TYPE_DEFAULT, Order::TYPE_BALANCE, Order::TYPE_BALANCE_AND_POINT, Order::TYPE_ALL_POINT])->first();
		if (!$order) {
			return $this->failed('订单不存在');
		}

		if ($order->status != Order::STATUS_PAY) {
			return $this->failed('订单未支付或已退款');
		}

		switch ($order->type) {
			case Order::TYPE_DEFAULT:
				$channel = Payment::TYPE_WX_LITE;
				break;
			case Order::TYPE_BALANCE:
				$channel = Payment::TYPE_BALANCE;
				break;
			case Order::TYPE_BALANCE_AND_POINT:
				$channel = Payment::TYPE_MIXED;
				break;
			case Order::TYPE_ALL_POINT:
				$channel = Payment::TYPE_POINT;
				break;
		}

		$service = app($channel);
		$result  = $service->refund($order, $clerk);
		if ($result['status']) {
			return $this->success('退款成功');
		} else {
			return $this->failed($result['message']);
		}
	}

	public function usedAt($code)
	{
		$coupon = Coupon::where('code', $code)->whereNull('used_at')->first();
		if (!$coupon) {
			return $this->failed('优惠券不存在或已使用');
		}

		$user = auth('shitang')->user();
		if (!$user || $user->status != 1) {
			return $this->failed('没有权限操作');
		}

		$coupon->used_at    = Carbon::now();
		$coupon->manager_id = $user->id;
		$coupon->save();

		return $this->success('核销成功');
	}

	public function invalidCoupons()
	{
		$limit = request('limit') ?: 15;

		$list = Coupon::where('manager_id', '>', 0)->whereNotNull('used_at')->with('discount')->with('clerk')->orderBy('used_at', 'DESC')->paginate($limit);

		return $this->response()->paginator($list, new CouponsTransformer());
	}
}