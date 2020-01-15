<?php

namespace GuoJiangClub\Catering\Component\User\Repository\Eloquent;

use GuoJiangClub\Catering\Component\User\Models\User;
use GuoJiangClub\Catering\Component\User\Repository\UserRepository;
use Illuminate\Support\Str;
use Prettus\Repository\Eloquent\BaseRepository;

class UserRepositoryEloquent extends BaseRepository implements UserRepository
{
	public function model()
	{
		return User::class;
	}

	/**
	 * Get a user by the given credentials.
	 *
	 * @param array $credentials
	 *
	 * @return mixed
	 */
	public function getUserByCredentials(array $credentials)
	{
		$query = $this->model;
		foreach ($credentials as $key => $value) {
			if (!Str::contains($key, 'password') and !empty($value)) {
				$query = $query->where($key, $value);
			}
		}

		return $query->first();
	}
}