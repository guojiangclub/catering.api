<?php

namespace GuoJiangClub\Catering\Component\Discount\Models;

use Carbon\Carbon;
use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discount extends Model implements DiscountContract
{
	use SoftDeletes;

	protected $guarded = ['id', 'orderAmountLimit'];

	protected $appends = ['is_enabled', 'use_start_time', 'use_end_time', 'action_type'];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.app.database.prefix', 'ibrand_');

		$this->setTable($prefix . 'discount');
	}

	public function rules()
	{
		return $this->hasMany(Rule::class);
	}

	public function actions()
	{
		return $this->hasMany(Action::class);
	}

	/**
	 * @return mixed
	 */
	public function hasRules()
	{
		return $this->rules()->count() !== 0;
	}

	public function isCouponBased()
	{
		return $this->coupon_based;
	}

	public function receiveCoupon()
	{
		$this->increment('used');
	}

	public function getActions()
	{
		return $this->actions;
	}

	public function getRules()
	{
		return $this->rules;
	}

	public function setCouponUsed()
	{
		return;
	}

	public function getIsEnabledAttribute()
	{
		if (!$this->status OR $this->usage_limit == 0 OR $this->ends_at < Carbon::now()) {
			return false;
		}

		return true;
	}

	public function getUsestartAtAttribute()
	{
		if (!$this->attributes['usestart_at']) {
			return $this->starts_at;
		}

		return $this->attributes['usestart_at'];
	}

	public function getUseStartTimeAttribute()
	{
		if (!$this->attributes['usestart_at']) {
			$time = $this->starts_at;
		} else {
			$time = $this->attributes['usestart_at'];
		}

		return date('Y-m-d', strtotime($time));
	}

	public function getUseEndTimeAttribute()
	{
		if (!$this->attributes['useend_at']) {
			$time = $this->ends_at;
		} else {
			$time = $this->attributes['useend_at'];
		}

		return date('Y-m-d', strtotime($time));
	}

	/**
	 * 为方便前台展示优惠券信息
	 *
	 * @return array
	 */
	public function getActionTypeAttribute()
	{
		$action = $this->actions()->first();
		$type   = [];

		if ($action->type == 'order_fixed_discount' || $action->type == 'goods_fixed_discount' || $action->type == 'market_order_fixed_discount') {
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