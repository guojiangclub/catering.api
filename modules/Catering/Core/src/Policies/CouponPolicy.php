<?php

namespace GuoJiangClub\Catering\Core\Policies;

use GuoJiangClub\Catering\Backend\Models\Coupon\Coupon;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User;

class CouponPolicy
{
	use HandlesAuthorization;

	public function update(User $user, Coupon $coupon)
	{
		return $user->id === $coupon->user_id;
	}
}