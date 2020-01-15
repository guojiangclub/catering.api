<?php

namespace GuoJiangClub\Catering\Core\Repository\Eloquent;

use GuoJiangClub\Catering\Component\User\Repository\UserRepository;
use GuoJiangClub\Catering\Core\Auth\User;
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
			if (!Str::contains($key, 'password') AND !empty($value)) {
				$query = $query->where($key, $value);
			}
		}

		return $query->first();
	}

	public function getUsersByCondition(array $where, $limit = 15, $with = [], $order_by = 'id', $sort_by = 'DESC')
	{
		$data = $this->scopeQuery(function ($query) use ($where, $order_by, $sort_by, $with) {
			if (is_array($where) && !empty($where)) {
				foreach ($where as $key => $value) {
					if (is_array($value)) {
						list($condition, $val) = $value;
						$query = $query->where($key, $condition, $val);
					} else {
						$query = $query->where($key, $value);
					}
				}
			}

			if (!empty($with)) {
				foreach ($with as $item) {
					$query = $query->with($item);
				}
			}

			return $query->orderBy($order_by, $sort_by);
		});

		if (0 == $limit) {
			return $data->get();
		}

		return $data->paginate($limit);
	}

}