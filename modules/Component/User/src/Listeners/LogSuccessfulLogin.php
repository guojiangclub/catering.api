<?php

namespace GuoJiangClub\EC\Catering\Component\User\Listeners;

use Carbon\Carbon;
use GuoJiangClub\Catering\Component\User\Models\User;
use GuoJiangClub\Catering\Component\User\Models\UserLoginLog;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
	public function handle(Login $event)
	{
		$user = $event->user;
		if (get_class($user) == User::class) {
			UserLoginLog::create([
				'user_id'    => $user->id,
				'ip'         => request()->ip(),
				'platform'   => request('open_type'),
				'login_time' => Carbon::now(),
			]);
		}
	}
}