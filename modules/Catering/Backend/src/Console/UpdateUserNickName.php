<?php

namespace GuoJiangClub\Catering\Backend\Console;

use ElementVip\Component\User\Repository\UserRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class UpdateUserNickName extends Command
{
	protected $signature = 'update:user_nick_name';

	protected $description = 'update user nick_name';

	public function handle(UserRepository $userRepository)
	{
		$users = $userRepository->all();
		if ($users && $users->count() > 0) {
			foreach ($users as $user) {
				if (!$user->nick_name) {
					continue;
				}

				if (Str::contains($user->nick_name, 'base64:')) {
					$user->nick_name = base64_decode(str_replace('base64:', '', $user->nick_name));
					$user->save();
				}
			}
		}
	}
}