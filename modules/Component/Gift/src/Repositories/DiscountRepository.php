<?php

namespace GuoJiangClub\Catering\Component\Gift\Repositories;

use GuoJiangClub\Catering\Component\Discount\Repositories\Eloquent\DiscountRepositoryEloquent;

class DiscountRepository extends DiscountRepositoryEloquent
{

	public function getDiscountLists($where, $orWhere)
	{
		return $this->scopeQuery(function ($query) use ($where, $orWhere) {
			$query = $query->Where(function ($query) use ($where) {
				if (is_array($where)) {
					foreach ($where as $key => $value) {
						if (is_array($value)) {
							list($operate, $va) = $value;
							$query = $query->where($key, $operate, $va);
						} else {
							$query = $query->where($key, $value);
						}
					}
				}
			});

			if (count($orWhere)) {
				$query = $query->orWhere(function ($query) use ($orWhere) {
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

			return $query->orderBy('created_at', 'desc');
		})->all();
	}

}