<?php

namespace GuoJiangClub\Catering\Server\Repositories;

use GuoJiangClub\Catering\Component\Recharge\Models\BalanceOrder;
use Prettus\Repository\Eloquent\BaseRepository;

class BalanceOrderRepository extends BaseRepository
{
	public function model()
	{
		return BalanceOrder::class;
	}

	public function getOrderByNo($order_no)
	{
		return $this->with('user')->findByField('order_no', $order_no)->first();
	}

	public function getOrdersByCondition(array $where, array $time, $limit = 15, $with = [], $order_by = 'id', $sort_by = 'DESC')
	{
		$data = $this->scopeQuery(function ($query) use ($where, $time, $order_by, $sort_by, $with) {
			if (is_array($where) && !empty($where)) {
				foreach ($where as $key => $value) {
					if ($key != 'mobile') {
						if (is_array($value)) {
							list($condition, $val) = $value;
							$query = $query->where($key, $condition, $val);
						} else {
							$query = $query->where($key, $value);
						}
					}
				}
			}

			if (is_array($time) && !empty($time)) {
				foreach ($time as $k => $v) {
					if (is_array($v)) {
						list($c, $va) = $v;
						$query = $query->where($k, $c, $va);
					} else {
						$query = $query->where($k, $v);
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

		if (isset($where['mobile']) && $where['mobile']) {
			$data->whereHas('user', function ($query) use ($where) {
				$query->where(function ($query) use ($where) {
					list($operate, $va) = $where['mobile'];
					$query->where('el_user.mobile', $operate, $va);
				});
			});
		}

		if (0 == $limit) {
			return $data->get();
		}

		return $data->paginate($limit);
	}
}