<?php

namespace GuoJiangClub\Catering\Component\Balance\Repository;

use Prettus\Repository\Eloquent\BaseRepository;
use GuoJiangClub\Catering\Component\Balance\Model\Balance;

class BalanceRepository extends BaseRepository
{

	/**
	 * Specify Model class name
	 *
	 * @return string
	 */
	public function model()
	{
		return Balance::class;
	}

	/**
	 * get user's balance
	 *
	 * @return int
	 * */
	public function getSum($id, $type = null)
	{
		if ($type !== null) {
			$sum = $this->model->where([
				'user_id' => $id,
				'type'    => $type,
			])->sum();
		} else {
			$sum = $this->model->where('user_id', $id)->sumBalance();
		}
		if ($sum) {
			return $this->getSumNumeric($sum);
		} else {
			return 0;
		}
	}

	public function fluctuation($user_id, $type = 'recharge')
	{
		/*if ($type == 'consume') {
			return $this->model->where('user_id', $user_id)->where('value', '<', 0)->orderBy('created_at', 'desc');
		}*/

		return $this->model->where('user_id', $user_id)->orderBy('created_at', 'desc');
	}

	public function addRecord($arr)
	{
		return $this->model->create($arr);
	}

	private function getSumNumeric($sum)
	{
		if (!is_numeric($sum)) {
			return 0;
		}

		return (int) $sum;
	}
}