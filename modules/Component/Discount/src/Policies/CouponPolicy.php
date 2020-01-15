<?php

namespace GuoJiangClub\Catering\Component\Discount\Policies;

use GuoJiangClub\Catering\Component\Discount\Models\Coupon;
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