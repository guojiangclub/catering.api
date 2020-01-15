<?php

namespace GuoJiangClub\Catering\Component\Order\Models;

use GuoJiangClub\Catering\Component\Point\Model\PointGoods;
use GuoJiangClub\Catering\Component\Product\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model implements Transformable
{
	use SoftDeletes;
	use TransformableTrait;

	protected $guarded = ['id'];

	protected $appends = ['is_refunded', 'item_sku',
	                      'item_category', 'units_total_yuan', 'total_yuan', 'redeem_point'];

	protected $model;

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.app.database.prefix', 'ibrand_');

		$this->setTable($prefix . 'order_item');
	}

	public function order()
	{
		return $this->belongsTo(Order::class);
	}

	public function product()
	{
		return $this->belongsTo(Product::class, 'item_id');
	}

	public function getModel()
	{
		if ($this->model) {
			return $this->model;
		}

		if ($this->product) {
			$this->model = $this->product;

			return $this->product;
		}

		$model       = $this->type;
		$model       = new $model();
		$this->model = $model->find($this->item_id);

		return $this->model;
	}

	public function getItemKey($type = 'sku')
	{
		return $this->getModel()->getKeyCode($type);
	}

	public function getItemId()
	{
		return $this->getModel()->getDetailIdAttribute();
	}

	public function getTotal()
	{
		return $this->total;
	}

	public function setUnitPriceAttribute($value)
	{
		$this->attributes['unit_price'] = $value * 100;
		$this->recalculateUnitsTotal();
	}

	/**
	 *  Recalculates total after units total or adjustments total change.
	 */
	public function recalculateTotal()
	{
		$this->total = $this->units_total + $this->adjustments_total;

		if ($this->total < 0) {
			$this->total = 0;
		}

		if (null !== $this->order) {
			$this->order->recalculateItemsTotal();
		}
	}

	public function recalculateAdjustmentsTotal()
	{
		$this->adjustments_total = $this->divide_order_discount + $this->item_discount;

		$this->recalculateTotal();
	}

	public function recalculateUnitsTotal()
	{
		$this->units_total = $this->quantity * $this->unit_price;

		$this->recalculateTotal();
	}

	public function setItemMetaAttribute($value)
	{
		$this->attributes['item_meta'] = json_encode($value);
	}

	public function getItemMetaAttribute($value)
	{
		return json_decode($value, true);
	}

	public function getIsRefundedAttribute()
	{
		/*return $this->refunds->count() > 0;
		 是否在列表显示item*/
		$count = $this->refunds->count();
		if ($count == 0) { //如果一次售后都未申请过
			return false;
		}
		if ($count == 2) {  //如果已经申请过2次售后
			return true;
		}
		if ($count == 1 AND
			(!in_array($this->refunds->first()->status, [2, 4])
				OR count($this->refunds->first()->logs->where('action', 'reject')) > 0)
		) {
			//如果申请了一次售后，并且未拒绝、未关闭
			return true;
		}

		return false;
	}

	public function getItemSkuAttribute()
	{
		if ($model = $this->getModel()) {
			if ($this->type == 'GuoJiangClub\\Catering\\Component\\Product\\Models\\Product') {
				return $this->getItemKey();
			} elseif ($this->type == 'GuoJiangClub\\Catering\\Component\\Product\\Models\\Goods') {
				return $model->goods_no;
			}
		}

		return null;
	}

	public function getItemCategoryAttribute()
	{
		return null;
	}

	/**
	 * 判断这个订单商品是否能够获得积分
	 *
	 * @param $pointInvalidRatio  判断积分有效费率
	 *
	 * @return bool
	 */
	public function isCanGetPoint($pointInvalidRatio)
	{
		return $this->units_total > 0
			AND ($this->total / $this->quantity) >= ($this->getModel()->market_price * $pointInvalidRatio);
	}

	/**
	 * 获取到这个订单能够正常获取到的积分
	 */
	public function getPoint()
	{
		$itemId = $this->getItemId();

		$pointGoods = PointGoods::ofItem($itemId)->first();

		if ($pointGoods AND $pointGoods->value != 0 AND $pointGoods->status == 1) {

			return $pointGoods->getPoint($this->total);
		}

		if (settings('point_goods_enabled') AND settings('point_goods_ratio')) {
			return ($this->total * settings('point_goods_ratio')) / 10000;
		}

		return 0;
	}

	public function getUnitsTotalYuanAttribute()
	{
		return number_format($this->units_total / 100, 2, ".", "");
	}

	public function getTotalYuanAttribute()
	{
		return number_format($this->total / 100, 2, ".", "");
	}

	public function getRedeemPointAttribute()
	{
		if ($model = $this->getModel()) {
			return $model->redeem_point;
		}

		return 0;
	}

}
