<?php

namespace GuoJiangClub\EC\Catering\Backend\Models;

use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends \GuoJiangClub\Catering\Component\Order\Models\OrderItem implements Transformable
{
	use SoftDeletes;
	use TransformableTrait;

	protected $guarded = ['id'];

	public function order()
	{
		return $this->belongsTo(Order::class, 'order_id')->withDefault();
	}

	public function product()
	{
		return $this->hasOne(Product::class, 'id', 'item_id');
	}

	/**
	 * 获取订单商品信息
	 */
	public function getItemInfoAttribute()
	{
		return json_decode($this->attributes['item_meta'], true);
	}

	public function getUnitPriceAttribute($value)
	{
		return $value / 100;
	}

	public function getTotalAttribute($value)
	{
		return $value / 100;
	}

	public function getAdjustmentsTotalAttribute($value)
	{
		return $value / 100;
	}

	public function getModel()
	{
		$model = $this->type;
		$model = new $model();

		return $model->find($this->item_id);
	}

	public function getItemKey($type = 'sku')
	{
		if ($model = $this->getModel()) {
			return $model->getKeyCode($type);
		}

		return 0;
	}

	public function getItemKeyAttribute()
	{
		return $this->getItemKey('spu');
	}

	public function shipping()
	{
		return $this->belongsTo(Shipping::class);
	}

	public function supplier()
	{
		return $this->belongsTo(Supplier::class);
	}
}
