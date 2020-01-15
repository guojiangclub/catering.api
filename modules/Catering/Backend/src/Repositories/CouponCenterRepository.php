<?php

namespace GuoJiangClub\Catering\Backend\Repositories;

use GuoJiangClub\Catering\Backend\Models\CouponCenter;
use Prettus\Repository\Eloquent\BaseRepository;

class CouponCenterRepository extends BaseRepository
{
	public function model()
	{
		return CouponCenter::class;
	}

	public function getActivityPaginate($where = [], $orWhere = [], $with = [], $limit = 15)
	{
		$query = $this->scopeQuery(function ($query) use ($where, $orWhere, $with) {
			if (is_array($where) && !empty($where)) {
				foreach ($where as $key => $value) {
					if (is_array($value)) {
						list($operate, $va) = $value;
						$query = $query->where($key, $operate, $va);
					} else {
						$query = $query->where($key, $value);
					}
				}
			}

			if (count($orWhere)) {
				$query->orWhere(function ($query) use ($orWhere) {
					if (is_array($orWhere)) {
						foreach ($orWhere as $key => $value) {
							if (is_array($value)) {
								list($operate, $va) = $value;
								$query = $query->where($key, $operate, $va);
							} else {
								$query = $query->where($key, $value);
							}
						}
					}
				});
			}

			if (is_array($with) && !empty($with)) {
				foreach ($with as $item) {
					$query = $query->with($item);
				}
			}

			return $query->orderBy('created_at', 'desc');
		});
		if ($limit) {
			return $query->paginate($limit);
		}

		return $query->all();
	}
}