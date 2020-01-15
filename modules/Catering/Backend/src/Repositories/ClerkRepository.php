<?php

namespace GuoJiangClub\Catering\Backend\Repositories;

use GuoJiangClub\Catering\Backend\Models\Clerk;
use Prettus\Repository\Eloquent\BaseRepository;

class ClerkRepository extends BaseRepository
{
	public function model()
	{
		return Clerk::class;
	}

	public function getClerkList($where)
	{
		return $this->model->where(function ($query) use ($where) {
			if (is_array($where)) {
				foreach ($where as $key => $value) {
					if (is_array($value)) {
						list($operate, $va) = $value;
						$query = $query->Where($key, $operate, $va);
					} else {
						$query = $query->where($key, $value);
					}
				}
			}

			return $query->orderBy('created_at', 'DESC');
		})->get();
	}
}