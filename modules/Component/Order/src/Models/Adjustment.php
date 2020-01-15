<?php

namespace GuoJiangClub\Catering\Component\Order\Models;

use GuoJiangClub\Catering\Component\Discount\Models\Discount;
use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class Adjustment extends Model implements Transformable
{
	const ORDER_DISCOUNT_ADJUSTMENT       = 'order_discount';
	const ORDER_ITEM_DISCOUNT_ADJUSTMENT  = 'order_item_discount';
	const ORDER_POINT_DISCOUNT_ADJUSTMENT = 'order_point_discount';

	use SoftDeletes;
	use TransformableTrait;

	protected $guarded = ['id'];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.app.database.prefix', 'ibrand_');

		$this->setTable($prefix . 'order_adjustment');
	}

	public function order()
	{
		return $this->belongsTo(Order::class);
	}

	public function orderItem()
	{
		return $this->belongsTo(OrderItem::class);
	}

	public function orderItemUnit()
	{
		return $this->belongsTo(OrderItemUnit::class);
	}

	public function discount()
	{
		return $this->hasOne(Discount::class);
	}
}
