<?php

namespace GuoJiangClub\Catering\Backend\Models;

use Illuminate\Database\Eloquent\Model;

class CouponCenter extends Model
{
	protected $table = 'st_coupon_center';

	protected $guarded = ['id'];

	public function items()
	{
		return $this->hasMany(CouponCenterItem::class, 'coupon_center_id');
	}
}