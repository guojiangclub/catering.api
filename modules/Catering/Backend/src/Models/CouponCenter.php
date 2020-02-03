<?php

namespace GuoJiangClub\Catering\Backend\Models;

use Illuminate\Database\Eloquent\Model;

class CouponCenter extends Model
{
	protected $guarded = ['id'];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.shitang-api.database.prefix', 'ca_');

		$this->setTable($prefix . 'coupon_center');
	}

	public function items()
	{
		return $this->hasMany(CouponCenterItem::class, 'coupon_center_id');
	}
}