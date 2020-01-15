<?php

namespace GuoJiangClub\Catering\Server\Http\Controllers;

use GuoJiangClub\Catering\Server\Repositories\OrderRepository;
use GuoJiangClub\Catering\Backend\Models\Order;
use GuoJiangClub\Catering\Server\Transformers\OrderTransformer;

class OrderController extends Controller
{
	protected $orderRepository;

	public function __construct(OrderRepository $orderRepository)
	{
		$this->orderRepository = $orderRepository;
	}

	public function list()
	{
		$limit = request('limit') ? request('limit') : 15;

		$user = request()->user();

		$list = $this->orderRepository->getOrdersByCondition(['user_id' => $user->id, 'status' => ['>', 0]], [], $limit, ['user']);

		return $this->response()->paginator($list, new OrderTransformer());
	}

	public function detail($order_no)
	{
		$order = $this->orderRepository->getOrderByNo($order_no);
		if (!$order) {
			return $this->failed('订单不存在');
		}

		return $this->success($order);
	}
}