<?php

namespace GuoJiangClub\Catering\Component\Gift\Models;

use Illuminate\Database\Eloquent\Model;
use \GuoJiangClub\Catering\Component\Discount\Models\Discount;

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
		return $this->hasMany(GiftCouponReceive::class, 'type_id')->where('type', 'gift_directional_coupon');
	}

}

