<?php

namespace GuoJiangClub\Catering\Core\Models;

use GuoJiangClub\Catering\Backend\Models\Coupon\Discount;
use Illuminate\Database\Eloquent\Model;

class GiftDirectionalCoupon extends Model
{
	protected $guarded = ['id'];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.app.database.prefix', 'ibrand_');

		$this->setTable($prefix . 'gift_directional_coupon');
	}

	public function coupon()
	{
		return $this->hasOne(Discount::class, 'id', 'coupon_id');
	}

	public function receive()
	{
		return $this->hasMany(GiftCouponReceive::class, 'origin_id')->where('origin_type', 'gift_directional_coupon');
	}

}

