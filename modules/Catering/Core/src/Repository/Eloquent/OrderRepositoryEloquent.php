<?php

namespace GuoJiangClub\Catering\Core\Repository\Eloquent;

use GuoJiangClub\Catering\Component\Order\Repositories\OrderRepository;
use GuoJiangClub\Catering\Component\Order\Repositories\Eloquent\OrderRepositoryEloquent as BaseOrderRepositoryEloquent;
use GuoJiangClub\Catering\Backend\Models\Order;

class OrderRepositoryEloquent extends BaseOrderRepositoryEloquent implements OrderRepository
{
	public function model()
	{
		return Order::class;
	}

	public function getOrderByNo($order_no)
	{
		return $this->findByField('order_no', $order_no)->first();
	}
}