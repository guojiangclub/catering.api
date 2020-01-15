<?php

namespace GuoJiangClub\Catering\Component\Recharge\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Carbon\Carbon;
use GuoJiangClub\Catering\Component\Recharge\Models\RechargeRule;

class RechargeRuleRepository extends BaseRepository
{
	/**
	 * Specify Model class name
	 *
	 * @return string
	 */
	public function model()
	{
		return RechargeRule::class;
	}

	public function getAll($name = null)
	{

		if (empty($name)) {
			return $this->model
				->where('type', "gift_recharge")
				->OrderBy('sort')->OrderBy('created_at')
				->with('gift')
				->with('gift.coupon')
				->with(['gift' => function ($query) {
					$query->where('type', "gift_recharge");
				}])
				->paginate(15);
		}

		return $this->model
			->where('type', "gift_recharge")
			->where('name', 'like', "$name%")
			->OrderBy('sort')->OrderBy('created_at')
			->with('gift')
			->with('gift.coupon')
			->with(['gift' => function ($query) {
				$query->where('type', "gift_recharge");
			}])
			->paginate(15);
	}

	public function getEffective()
	{

		return $this->model->where('type', "gift_recharge")->where('status', 1)->OrderBy('sort')->OrderBy('created_at')
			->with('gift')
			->with('gift.coupon')
			->with(['gift' => function ($query) {
				$query->where('type', "gift_recharge");
			}])
			->get();
	}

	public function getEffectiveByID($id, $payment_amount, $amount)
	{
		return $this->model->where([
			'type'           => "gift_recharge",
			'payment_amount' => $payment_amount,
			'amount'         => $amount,
			'id'             => $id,
			'status'         => 1,
		])
			->first();
	}

	public function getRechargeByID($id)
	{
		return $this->model->where('type', "gift_recharge")->where('id', $id)->where('status', 1)
			->with('gift')
			->with('gift.coupon')
			->with(['gift' => function ($query) {
				$query->where('type', "gift_recharge");
			}])
			->first();
	}

	public function getRechargeByIDStatusOff($id)
	{
		return $this->model->where('type', "gift_recharge")->where('id', $id)
			->with('gift')
			->with('gift.coupon')
			->with(['gift' => function ($query) {
				$query->where('type', "gift_recharge");
			}])
			->first();
	}

}
