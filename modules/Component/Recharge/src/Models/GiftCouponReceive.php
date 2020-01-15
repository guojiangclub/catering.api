<?php

namespace GuoJiangClub\Catering\Component\Recharge\Models;

use Illuminate\Database\Eloquent\Model;

class GiftCouponReceive extends Model
{
	protected $guarded = ['id'];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.app.database.prefix', 'ibrand_');

		$this->setTable($prefix . 'gift_coupon_receive');
	}
}

