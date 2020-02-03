<?php

namespace GuoJiangClub\Catering\Core\Models;

use GuoJiangClub\Catering\Core\Auth\User;
use GuoJiangClub\Catering\Backend\Models\Coupon\Coupon;
use Illuminate\Database\Eloquent\Model;

class GiftCouponReceive extends Model
{
	public $guarded = ['id'];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.shitang-api.database.prefix', 'ca_');

		$this->setTable($prefix . 'gift_coupon_receive');
	}

	public function coupon()
	{
		return $this->belongsTo(Coupon::class, 'discount_id', 'discount_id');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id', 'id');
	}
}