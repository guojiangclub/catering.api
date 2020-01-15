<?php

namespace GuoJiangClub\Catering\Component\Discount\Models;

use GuoJiangClub\Catering\Component\Discount\Contracts\DiscountContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Coupon extends Model implements DiscountContract
{
	use SoftDeletes;

	protected $guarded = ['id', 'orderAmountLimit'];

	protected $appends = ['discount_amount', 'discount_percentage', 'is_expire'];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$prefix = config('ibrand.app.database.prefix', 'ibrand_');

		$this->setTable($prefix . 'discount_coupon');
	}

	public function discount()
	{
		return $this->belongsTo('GuoJiangClub\Catering\Component\Discount\Models\Discount');
	}

	/**
	 * @return mixed
	 */
	public function hasRules()
	{
		return $this->discount->rules()->count() !== 0;
	}

	public function isCouponBased()
	{
		return $this->discount->coupon_based;
	}

	public function getLabelAttribute()
	{
		return $this->discount->label;
	}

	public function getStartsAtAttribute()
	{
		return $this->discount->usestart_at;
	}

	public function getIsExpireAttribute()
	{
		if ($this->discount->status === 0) {
			return true;
		}

		if (!empty($this->expires_at) AND $this->expires_at < Carbon::now()) {
			return true;
		}

		if ($this->discount->useend_at < Carbon::now()) {
			return true;
		}

		return false;
	}

	public function getEndsAtAttribute()
	{
		if (empty($this->expires_at)) {
			if (empty($this->discounts->useend_at)) {
				return $this->discount->ends_at;
			} else {
				return $this->discount->useend_at;
			}
		}

		return $this->expires_at;
	}

	public function getDiscountAmountAttribute()
	{
		if ($action = $this->discount->actions()->first() AND Str::contains($action->type, 'fixed')) {
			return json_decode($action->configuration)->amount;
		}

		return 0;
	}

	public function getDiscountPercentageAttribute()
	{
		if ($action = $this->discount->actions()->first() AND Str::contains($action->type, 'percentage')) {
			return json_decode($action->configuration)->percentage;
		}

		return 100;
	}

	public function getActions()
	{
		return $this->discount->getActions();
	}

	public function getRules()
	{
		return $this->discount->rules;
	}

	public function setCouponUsed()
	{
		$this->used_at = Carbon::now();
		$this->save();
	}

}