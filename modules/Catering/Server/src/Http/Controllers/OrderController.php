<?php

namespace GuoJiangClub\Catering\Server\Http\Controllers;

use GuoJiangClub\Catering\Server\Repositories\OrderRepository;
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

    public function getPointOrders()
    {
        $orderConditions = [];

        if (request('status')) {
            $orderConditions['status'] = request('status');
        } else {
            $orderConditions ['status'] = ['status', '<>', 0];
            $orderConditions ['status2'] = ['status', '<>', 9];
        }

        $offline = request('offline') ? request('offline') : 0;

        $type = request('type') ? request('type') : [5];

        $orderConditions ['user_id'] = request()->user()->id;

        $itemConditions = [];

        $limit = request('limit') ? request('limit') : 15;

        $order = $this->orderRepository->getOrdersByConditions($orderConditions, $itemConditions, $limit, ['items'], $offline, $type);

        return $this->response()->paginator($order, new OrderTransformer());
    }
}