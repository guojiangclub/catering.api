<?php

namespace ElementVip\Server\Repositories;

use ElementVip\Store\Backend\Model\TravelContents;
use Prettus\Repository\Eloquent\BaseRepository;

class TravelContentRepository extends BaseRepository
{
	public function model()
	{
		return TravelContents::class;
	}

	public function getContentsPaginate(array $where, $order_by = 'id', $sort_by = 'DESC', $limit = 15)
	{
		$data = $this->scopeQuery(function ($query) use ($where, $order_by, $sort_by) {
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

			return $query->orderBy('is_recommend', 'DESC')->orderBy($order_by, $sort_by);
		});

		if (0 == $limit) {
			return $data->all();
		}

		return $data->paginate($limit);
	}
}