<?php

namespace GuoJiangClub\Catering\Server\Repositories;

use GuoJiangClub\Catering\Backend\Models\Order;
use GuoJiangClub\Catering\Backend\Models\Payment;
use Prettus\Repository\Eloquent\BaseRepository;

class OrderRepository extends BaseRepository
{
	public function model()
	{
		return Order::class;
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
					if ($key != 'mobile' && $key != 'balance') {
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
					$query->where(config('ibrand.app.database.prefix', 'ibrand_').'user.mobile', $operate, $va);
				});
			});
		}

		if (isset($where['balance']) && $where['balance']) {
			$data->whereHas('payments', function ($query) use ($where) {
				$query->where(function ($query) {
					$query->where(config('ibrand.app.database.prefix', 'ibrand_').'payment.channel', Payment::TYPE_BALANCE)->where(config('ibrand.app.database.prefix', 'ibrand_').'payment.status', Payment::STATUS_COMPLETED);
				});
			});
		}

		if (0 == $limit) {
			return $data->get();
		}

		return $data->paginate($limit);
	}
}