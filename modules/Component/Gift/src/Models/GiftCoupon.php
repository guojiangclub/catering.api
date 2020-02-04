<?php

namespace GuoJiangClub\Catering\Component\Gift\Models;

use Illuminate\Database\Eloquent\Model;
use \GuoJiangClub\Catering\Component\Discount\Models\Discount;

class GiftCoupon extends Model
{
	protected $guarded = ['id'];

	protected $appends = ['is_receive_coupon'];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.app.database.prefix', 'ibrand_');

		$this->setTable($prefix . 'gift_coupon');
	}

	public function coupon()
	{
		return $this->hasOne(Discount::class, 'id', 'coupon_id');
	}

	public function receive()
	{
		return $this->hasMany(GiftCouponReceive::class, 'gift_coupon_id');
	}

	public function getIsReceiveCouponAttribute()
	{
		if (count($this->receive) > 0) {
			return true;
		}

		return false;
	}

}

