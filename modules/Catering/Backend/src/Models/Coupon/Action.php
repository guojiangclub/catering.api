<?php

namespace GuoJiangClub\Catering\Backend\Models\Coupon;

use GuoJiangClub\Catering\Component\Discount\Models\Action as BaseAction;

class Action extends BaseAction
{
	public function setConfigurationAttribute($value)
	{
		$type = $this->attributes['type'];

		if ($type == 'hot_order_fixed_discount') {
			$this->attributes['configuration'] = json_encode(['amount' => $value * 100]);
		} else {

			$this->attributes['configuration'] = json_encode(['percentage' => $value]);
		}
	}

	public function getActionValueAttribute()
	{
		$value = json_decode($this->attributes['configuration'], true);
		$keys  = array_keys($value);

		foreach ($keys as $val) {
			if ($val == 'amount') {
				return $value['amount'] / 100;
			} else {
				return $value['percentage'];
			}
		}
	}

	public function getActionTypeAttribute()
	{
		$type = '';
		switch ($this->type) {
			case 'order_fixed_discount':
				$type = '订单减金额';
				break;
			case 'hot_order_fixed_discount':
				$type = '订单减金额';
				break;
			case 'order_percentage_discount':
				$type = '订单打折';
				break;
			case 'market_order_percentage_discount':
				$type = '订单打折';
				break;
		}

		return $type;
	}

	public function getActionTextAttribute()
	{
		$type  = $this->action_type;
		$value = json_decode($this->attributes['configuration'], true);
		$keys  = array_keys($value);

		foreach ($keys as $val) {
			if ($val == 'amount') {
				return $type . ($value['amount'] / 100) . '元';
			} else {
				return $type . $value['percentage'] . '%';
			}
		}
	}
}