<?php

namespace GuoJiangClub\Catering\Backend\Models\Coupon;

use GuoJiangClub\Catering\Component\Discount\Models\Rule as BaseRule;

class Rule extends BaseRule
{
	public $timestamps = false;

	protected $guarded = ['id'];

	public function setConfigurationAttribute($value)
	{
		$type = $this->attributes['type'];

		if ($type == 'contains_market_shop' || $type == 'exclude_market_shop') {
			$this->attributes['configuration'] = json_encode(['shops' => $value]);
		} elseif ($type == 'contains_market') {
			$this->attributes['configuration'] = json_encode(['markets' => $value]);
		} else {
			$this->attributes['configuration'] = json_encode(['amount' => $value * 100]);
		}
	}

	public function getRulesValueAttribute()
	{
		$type  = $this->attributes['type'];
		$value = json_decode($this->attributes['configuration'], true);

		if ($type == 'contains_product' OR $type == 'goods_id') {
			return $value;
		} elseif ($type == 'contains_category') {
			return $value;
		} else {
			return array_values($value)[0];
		}
	}
}