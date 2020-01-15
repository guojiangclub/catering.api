<?php

namespace GuoJiangClub\Catering\Backend\Models\Coupon;

use GuoJiangClub\Catering\Component\Discount\Models\Coupon as BaseCoupon;
use GuoJiangClub\Catering\Component\User\Models\Relations\BelongToUserTrait;
use GuoJiangClub\Catering\Backend\Models\Clerk;
use Illuminate\Support\Str;

class Coupon extends BaseCoupon
{
	use BelongToUserTrait;

	protected $appends = ['discount_amount', 'discount_percentage', 'starts_at', 'ends_at', 'use_start_time', 'use_end_time'];

	public function discount()
	{
		return $this->belongsTo(Discount::class);
	}

	public function clerk()
	{
		return $this->belongsTo(Clerk::class, 'manager_id', 'id');
	}

	public function order()
	{
		return $this->belongsToMany('GuoJiangClub\Catering\Component\Order\Models\Order', 'el_order_adjustment', 'origin_id', 'order_id');
	}

	public function getOrder()
	{
		return $this->order()->wherePivot('origin_type', 'coupon')->first();
	}

	public function getDiscountAmountAttribute()
	{
		if ($action = $this->discount->actions()->first() AND Str::contains($action->type, 'fixed')) {
			return json_decode($action->configuration)->amount;
		}

		return 0;
	}

	public function getUseStartTimeAttribute()
	{
		if (strtotime($this->created_at) > strtotime($this->discount->usestart_at)) {
			return date('Y-m-d', strtotime($this->created_at));
		} else {
			return date('Y-m-d', strtotime($this->discount->usestart_at));
		}
	}

	public function getUseEndTimeAttribute()
	{
		if (empty($this->expires_at)) {
			if (empty($this->discount->useend_at)) {
				return date('Y-m-d', strtotime($this->discount->ends_at));
			} else {
				return date('Y-m-d', strtotime($this->discount->useend_at));
			}
		}

		return date('Y-m-d', strtotime($this->expires_at));
	}

}