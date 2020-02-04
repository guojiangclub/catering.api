<?php

namespace GuoJiangClub\Catering\Component\Gift\Services;

use Carbon\Carbon;
use GuoJiangClub\Catering\Component\User\Models\User;
use GuoJiangClub\Catering\Component\Gift\Repositories\OrderRepository;

class DirectionalCouponService
{
	private $orderRepository;

	public function __construct(OrderRepository $orderRepository

	)
	{
		$this->orderRepository = $orderRepository;
	}

	public function searchUserByMobile($mobile)
	{
		return User::whereIn('mobile', $mobile)->where('status', 1)->pluck('id')->toArray();
	}

	public function searchUserByMobileCount($mobile)
	{
		return User::whereIn('mobile', $mobile)->where('status', 1)->count();
	}

	public function searchUserByCustom($input, $count = true)
	{
		$user     = [];
		$where    = [];
		$orWhere  = [];
		$user_num = [];
		$group_id = empty($input['group_id']) ? null : $input['group_id'];

		if (empty($input['n_day_buy'])
			And empty($input['n_day_no_buy'])
			And empty($input['buy_price_above'])
			And empty($input['buy_price_below'])
			And empty($input['buy_num_below'])
			And empty($input['buy_num_above'])) {
			if (!empty($input['group_id'])) {
				$user = User::where('group_id', $input['group_id'])->where('status', 1)->pluck('id')->toArray();
			} else {
				$user = User::where('status', 1)->pluck('id')->toArray();
			}
			if ($count) {
				return count($user);
			}

			return $user;
		}

		if (!empty($input['n_day_buy'])
			|| !empty($input['n_day_no_buy'])
			|| !empty($input['buy_price_above'])
			|| !empty($input['buy_price_below'])
			|| !empty($input['buy_num_below'])
			|| !empty($input['buy_num_above'])) {
			if (!empty($input['group_id'])) {
				$where['group_id'] = ['=', $input['group_id']];
			}
		}

		if (!empty($input['n_day_buy']) And !empty($input['n_day_no_buy'])) {
			$time                = Carbon::now()->addDays(-$input['n_day_buy']);
			$arr                 = explode(' ', $time);
			$where['created_at'] = ['>=', $arr[0] . ' 00:00:00'];

			$time                  = Carbon::now()->addDays(-$input['n_day_no_buy']);
			$arr                   = explode(' ', $time);
			$orWhere['created_at'] = ['<=', $arr[0] . ' 00:00:00'];
		}

		if (!empty($input['n_day_buy']) And empty($input['n_day_no_buy'])) {
			$time                = Carbon::now()->addDays(-$input['n_day_buy']);
			$arr                 = explode(' ', $time);
			$where['created_at'] = ['>=', $arr[0] . ' 00:00:00'];
		}

		if (empty($input['n_day_buy']) And !empty($input['n_day_no_buy'])) {
			$time                = Carbon::now()->addDays(-$input['n_day_no_buy']);
			$arr                 = explode(' ', $time);
			$where['created_at'] = ['>=', $arr[0] . ' 00:00:00'];
		}

		if (empty($input['n_day_buy']) And empty($input['n_day_no_buy'])) {
			$time                = Carbon::now()->addDays(1);
			$arr                 = explode(' ', $time);
			$where['created_at'] = ['<=', $arr[0] . ' 00:00:00'];
		}

		if (!empty($input['buy_price_above'])) {
			$where['total'] = ['>=', $input['buy_price_above'] * 100];
		}

		if (!empty($input['buy_price_below'])) {
			$where['total'] = ['<=', $input['buy_price_below'] * 100];
		}

		if (!empty($input['buy_price_above']) And !empty($input['buy_price_below'])) {
			if ($input['buy_price_above'] == $input['buy_price_below']) {
				$where['total'] = ['=', $input['buy_price_below'] * 100];
			} else {
				$where['total']   = ['<=', $input['buy_price_below'] * 100];
				$orWhere['total'] = ['>=', $input['buy_price_above'] * 100];
			}
		}

		$user_order = $this->orderRepository->getOrderList($where, $orWhere);

		if (count($user_order) <= 0) {
			if ($count) {
				return count($user);
			}

			return $user;
		}

		if (empty($input['buy_num_above']) And empty($input['buy_num_below'])) {
			$arr = array_count_values($user_order);
			foreach ($arr as $k => $item) {
				if ($item >= 1) {
					$user_num[] = $k;
				}
			}
		}

		if (!empty($input['buy_num_above']) And empty($input['buy_num_below'])) {
			$arr = array_count_values($user_order);
			foreach ($arr as $k => $item) {
				if ($input['buy_num_above'] <= $item) {
					$user_num[] = $k;
				}
			}
		}

		if (!empty($input['buy_num_below']) And empty($input['buy_num_above'])) {
			$arr = array_count_values($user_order);
			foreach ($arr as $k => $item) {
				if ($input['buy_num_below'] >= $item) {
					$user_num[] = $k;
				}
			}
		}

		if (!empty($input['buy_num_above']) && !empty($input['buy_num_below'])) {
			$arr = array_count_values($user_order);
			if ($input['buy_num_above'] == $input['buy_num_below']) {
				foreach ($arr as $k => $item) {
					if ($input['buy_num_above'] == $item) {
						$user_num[] = $k;
					}
				}
			} else {
				foreach ($arr as $k => $item) {
					if ($input['buy_num_above'] <= $item And $item <= $input['buy_num_below']) {
						$user_num[] = $k;
					}
				}
			}
		}

		if (!empty($input['n_day_no_buy']) And empty($input['n_day_buy'])) {

			if (count($user_num) > 0) {
				if (empty($input['group_id'])) {
					$user = User::where('status', 1)->whereNotIn('id', $user_num)->pluck('id')->toArray();
				} else {
					$user = User::where('group_id', $input['group_id'])->where('status', 1)->whereNotIn('id', $user_num)->pluck('id')->toArray();
				}
			} else {
				if (empty($input['group_id'])) {
					$user = User::where('status', 1)->pluck('id')->toArray();
				} else {
					$user = User::where('group_id', $input['group_id'])->where('status', 1)->pluck('id')->toArray();
				}
			}

			if ($count) {
				return count($user);
			}

			return $user;
		}

		if ($count) {
			return count($user_num);
		}

		return $user_num;
	}

	public function getUserID($input)
	{
		$mobile  = [];
		$user_id = [];
		if ($input['directional_type'] == 'mobile') {
			$mobile_input = explode('#', trim($input['mobile']));
			foreach ($mobile_input as $k => $item) {
				if ($k < 80 And !empty($item)) {
					$mobile[$k] = trim($item);
				}
			}
			if (count($mobile) > 0) {
				$user_id = $this->searchUserByMobile($mobile);
			}
		}

		if ($input['directional_type'] == 'custom') {
			$user_id = $this->searchUserByCustom($input, false);
		}

		if (count($user_id) != $input['number']) {
			$rand        = array_rand($user_id, $input['number']);
			$new_user_id = [];
			foreach ($rand as $k => $item) {
				$new_user_id[$k] = $user_id[$item];
			}
			$user_id = $new_user_id;
		}

		if (count($user_id) > 1000) {
			$collection = collect($user_id);
			$chunks     = $collection->chunk(1000)->toArray();

			return $chunks;
		}

		return $user_id;
	}

}