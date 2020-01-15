<?php

namespace GuoJiangClub\Catering\Component\User\Observers;

use GuoJiangClub\Catering\Component\User\Models\User;

class UserObserver
{
	public function created(User $user)
	{
		if ($user->mobile) {
			//如果设置了手机号，则给积分。
			event('verify_mobile', $user);
		}
	}

}