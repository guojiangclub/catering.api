<?php

namespace GuoJiangClub\EC\Catering\Backend\Models;

use GuoJiangClub\Catering\Component\Discount\Models\Discount;
use GuoJiangClub\Catering\Component\Order\Models\Adjustment;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class OrderAdjustment extends Adjustment implements Transformable
{
	use TransformableTrait;

	protected $guarded = ['id'];

	public function discount()
	{
		return $this->hasOne(Discount::class, 'id', 'origin_id');
	}

	public function order()
	{
		return $this->hasOne(Order::class, 'id', 'order_id');
	}

}
