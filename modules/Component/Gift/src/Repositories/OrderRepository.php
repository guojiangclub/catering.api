<?php

namespace GuoJiangClub\Catering\Component\Gift\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use GuoJiangClub\Catering\Component\Order\Models\Order;

class OrderRepository extends BaseRepository
{

	/**
	 * Specify Model class name
	 *
	 * @return string
	 */
	public function model()
	{
		return Order::class;
	}

	public function getOrderList($where, $orWhere)
	{
		$query = $this->model->whereIn('status', [4, 5]);
		if (count($where) > 0) {
			foreach ($where as $key => $value) {
				if ($key != 'group_id') {
					if (is_array($value)) {
						list($operate, $va) = $value;
						$query = $query->where($key, $operate, $va);
					} else {
						$query = $query->where($key, $value);
					}
				}
			}
		}
		if (count($orWhere) > 0) {
			foreach ($orWhere as $key => $value) {
				if (is_array($value)) {
					list($operate, $va) = $value;
					$query = $query->where($key, $operate, $va);
				} else {
					$query = $query->where($key, $value);
				}
			}
		}
		$query->whereHas('user', function ($query) use ($where) {
			$query->where(function ($query) use ($where) {
				if (isset($where['group_id'])) {
					list($operate, $va) = $where['group_id'];
					$query->where('el_user.group_id', $operate, $va)->where('status', 1)->pluck('id');
				}
			});
		});

		return $query->pluck('user_id')->toArray();
	}

}
