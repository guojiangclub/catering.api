<?php

namespace GuoJiangClub\Catering\Component\Gift\Models;

use Illuminate\Database\Eloquent\Model;
use \GuoJiangClub\Catering\Component\Discount\Models\Coupon;
use GuoJiangClub\Catering\Component\User\Models\User;

class GiftCouponReceive extends Model
{
	protected $guarded = ['id'];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.app.database.prefix', 'ibrand_');

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

