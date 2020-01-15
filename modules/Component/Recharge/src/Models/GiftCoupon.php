<?php

namespace GuoJiangClub\Catering\Component\Recharge\Models;

use Illuminate\Database\Eloquent\Model;
use GuoJiangClub\Catering\Component\Discount\Models\Discount;

class GiftCoupon extends Model
{
	protected $guarded = ['id'];

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

}

