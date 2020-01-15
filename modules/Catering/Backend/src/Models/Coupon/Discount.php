<?php

namespace GuoJiangClub\Catering\Backend\Models\Coupon;

use Carbon\Carbon;
use GuoJiangClub\Catering\Component\Discount\Models\Discount as BaseDiscount;

class Discount extends BaseDiscount
{
	protected $appends = ['is_enabled', 'use_start_time', 'use_end_time', 'action_type', 'rule_type'];

	public function rules()
	{
		return $this->hasMany(Rule::class);
	}

	public function actions()
	{
		return $this->hasOne(Action::class);
	}

	public function coupons()
	{
		return $this->hasMany(Coupon::class, 'discount_id');
	}

	public function getDiscountActionAttribute()
	{
		return $this->actions()->where('type', '<>', 'goods_times_point')->first();
	}

	public function getDiscountItemTotalAttribute()
	{
		return $this->rules()->where('type', 'item_total')->first();
	}

	public function getContainsMarketShopAttribute()
	{
		return $this->rules()->where('type', 'contains_market_shop')->first();
	}

	public function getExcludeMarketShopAttribute()
	{
		return $this->rules()->where('type', 'exclude_market_shop')->first();
	}

	public function getContainsMarketAttribute()
	{
		return $this->rules()->where('type', 'contains_market')->first();
	}

	public function getUsedCouponCountAttribute()
	{
		return $this->coupons()->whereNotNull('used_at')->count();
	}

	public function getStatusTextAttribute()
	{
		$start  = $this->starts_at;
		$end    = $this->ends_at;
		$status = $this->status;

		if ($start > Carbon::now() AND $status == 1) {
			return '活动未开始';
		}

		if ($start <= Carbon::now() AND $end > Carbon::now() AND $status == 1) {
			return '活动进行中';
		}

		if ($status == 0 OR $end < Carbon::now()) {
			return '活动已结束';
		}

		return '';
	}

	public function getRuleTypeAttribute()
	{
		$rule = $this->rules()->where('type', 'item_total')->first();
		if ($rule) {
			return json_decode($rule->configuration, true);
		}

		return ['amount' => 0];
	}

	public function getActionTypeAttribute()
	{
		$action = $this->actions()->first();
		$type   = [];

		/*if ($this->coupon_based == 0) return $type;*/

		if ($action->type == 'order_fixed_discount' || $action->type == 'goods_fixed_discount' || $action->type == 'market_order_fixed_discount' || $action->type == 'hot_order_fixed_discount') {
			$type['type']  = 'cash';
			$type['value'] = json_decode($action->configuration, true)['amount'] / 100;
		} elseif (str_contains($action->type, 'activity_')) {
			return json_decode($action->configuration, true);
		} elseif ($action->type != 'goods_times_point') {
			$type['type']  = 'discount';
			$type['value'] = json_decode($action->configuration, true)['percentage'] / 10;
		}

		return $type;
	}
}