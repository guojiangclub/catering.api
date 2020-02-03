<?php

namespace GuoJiangClub\Catering\Backend\Models;

use GuoJiangClub\Catering\Backend\Models\Coupon\Discount;
use Illuminate\Database\Eloquent\Model;

class CouponCenterItem extends Model
{
	protected $guarded = ['id'];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.shitang-api.database.prefix', 'ca_');

		$this->setTable($prefix . 'coupon_center_item');
	}

	public function center()
	{
		return $this->belongsTo(CouponCenter::class, 'coupon_center_id');
	}

	public function discount()
	{
		return $this->belongsTo(Discount::class, 'discount_id');
	}
}