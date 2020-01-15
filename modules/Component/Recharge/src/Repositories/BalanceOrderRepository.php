<?php

namespace GuoJiangClub\Catering\Component\Recharge\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Carbon\Carbon;
use GuoJiangClub\Catering\Component\Recharge\Models\BalanceOrder;

class BalanceOrderRepository extends BaseRepository
{
	/**
	 * Specify Model class name
	 *
	 * @return string
	 */
	public function model()
	{
		return BalanceOrder::class;
	}

	public function getLists($where, $limit = 50, $time = [])
	{
		$query = $this->model->orderBy('created_at', 'desc');

		if (count($where) > 0) {
			foreach ($where as $key => $value) {
				if ($key != 'mobile') {
					if (is_array($value)) {
						list($operate, $va) = $value;
						$query = $query->where($key, $operate, $va);
					} else {
						$query = $query->where($key, $value);
					}
				}
			}
		}

		if (count($time) > 0) {
			foreach ($time as $key => $value) {
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
				if (isset($where['mobile'])) {
					list($operate, $va) = $where['mobile'];
					$query->where('el_user.mobile', $operate, $va);
				}
			});
		});

		$query = $query->with('recharge')->with('user')->where('pay_status', 1);

		if ($limit == 0) {
			return $query->get();
		} else {
			return $query->paginate($limit);
		}
	}

}



