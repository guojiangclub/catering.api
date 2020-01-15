<?php

namespace GuoJiangClub\Catering\Backend\Models;

use GuoJiangClub\Catering\Backend\Models\Coupon\Discount;
use Illuminate\Database\Eloquent\Model;

class CouponCenterItem extends Model
{
	protected $table = 'st_coupon_center_item';

	protected $guarded = ['id'];

	public function center()
	{
		return $this->belongsTo(CouponCenter::class, 'coupon_center_id');
	}

	public function discount()
	{
		return $this->belongsTo(Discount::class, 'discount_id');
	}
}