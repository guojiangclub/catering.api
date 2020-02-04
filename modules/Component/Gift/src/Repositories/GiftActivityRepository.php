<?php

namespace GuoJiangClub\Catering\Component\Gift\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Carbon\Carbon;
use GuoJiangClub\Catering\Component\Gift\Models\GiftActivity;
use GuoJiangClub\Catering\Component\Gift\Models\GiftCouponReceive;
use GuoJiangClub\Catering\Component\Order\Models\Order;

class GiftActivityRepository extends BaseRepository
{
	/**
	 * Specify Model class name
	 *
	 * @return string
	 */
	public function model()
	{
		return GiftActivity::class;
	}

	public function giftAll($type)
	{
		return $this->model->where('type', $type)->OrderBy('status', 'desc')->OrderBy('created_at', 'desc')
			->with('gift')
			->with('gift.coupon')
			->with(['gift' => function ($query) use ($type) {
				$query->where('type', $type);
			}])
			->paginate(20);
	}

	public function giftListEffective($type, $user = null)
	{
		if (!$user) {
			return $this->model->where('type', $type)->where('status', 1)
				->where('ends_at', '>=', Carbon::now())
				->where('starts_at', '<=', Carbon::now())
				->with('gift')
				->with('gift.coupon')
				->with(['gift' => function ($query) use ($type) {
					$query->where('type', $type);
				}])
				->first();
		}

		return $this->model->where('type', $type)->where('status', 1)
			->where('ends_at', '>=', Carbon::now())
			->where('starts_at', '<=', Carbon::now())
			->with('gift')
			->with('gift.coupon')
			->with(['gift' => function ($query) use ($type) {
				$query->where('type', $type);
			}])
			->with(['gift.receive' => function ($query) use ($type, $user) {
				$query->where('type', $type)->where('user_id', $user->id);
			}])
			->first();
	}

	public function giftListEffectiveGiftBirthday($user_id)
	{

		$time = Carbon::now()->timestamp;
		$date = date('Y-', $time);

		return $this->model->where('type', 'gift_birthday')->where('status', 1)
			->where('ends_at', '>=', Carbon::now())
			->where('starts_at', '<=', Carbon::now())
			->with('gift')
			->with('gift.coupon')
			->with(['gift' => function ($query) {
				$query->where('type', 'gift_birthday');
			}])
			->with(['gift.receive' => function ($query) use ($user_id, $date) {
				$query->where('type', 'gift_birthday')->where('user_id', $user_id)->where('created_at', 'like', "$date%");
			}])
			->first();
	}

	public function getListByIDStatus($id, $type)
	{
		return $this->model->where('type', $type)->where('id', $id)->where('status', 1)
			->with('gift')
			->with('gift.coupon')
			->with(['gift' => function ($query) use ($type) {
				$query->where('type', $type);
			}])
			->first();
	}

	public function getListByID($id, $type)
	{
		return $this->model->where('type', $type)->where('id', $id)
			->with('gift')
			->with('gift.coupon')
			->with(['gift' => function ($query) use ($type) {
				$query->where('type', $type);
			}])
			->first();
	}

	public function checkAllowCreateGiftNewUser($date)
	{
		$list = $this->model->where('type', 'gift_new_user')->where('status', 1)->get();
		if (count($list) == 0) {
			return true;
		} elseif (count($list) == 1) {
			if ($date == date('Y-m-d H:i:s', strtotime($list->first()->ends_at) + 1) && Carbon::now() <= $list->first()->ends_at && Carbon::now() >= $list->first()->starts_at) {
				return true;
			}
		}

		return false;
	}

	public function checkAllowCreateGiftBirthday($date)
	{
		$list = $this->model->where('type', 'gift_birthday')->where('status', 1)->get();
		if (count($list) == 0) {
			return true;
		} elseif (count($list) == 1) {
			if ($date == date('Y-m-d H:i:s', strtotime($list->first()->ends_at) + 1) && Carbon::now() <= $list->first()->ends_at && Carbon::now() >= $list->first()->starts_at) {
				return true;
			}
		}

		return false;
	}

	public function DateProcessingGiftNewUser($user)
	{
		if ($gift_new_user = $this->giftListEffective('gift_new_user', $user)) {
			$receive                    = GiftCouponReceive::where('user_id', $user->id)->where('type', 'gift_new_user')->count();
			$gift_new_user->is_receive  = $receive ? true : false;
			$order                      = [Order::STATUS_PAY, Order::STATUS_DELIVERED, Order::STATUS_RECEIVED, Order::STATUS_COMPLETE, Order::STATUS_REFUND];
			$orders                     = Order::where(['user_id' => $user->id])->whereIn('status', $order)->count();
			$gift_new_user->is_new_user = $orders || $receive ? false : true;

			return $gift_new_user;
		}

		return null;
	}

	public function DateProcessingGiftBirthday($user)
	{
		if ($gift = $this->giftListEffectiveGiftBirthday($user->id)) {
			$receive          = GiftCouponReceive::where('user_id', $user->id)->where('type', 'gift_birthday')->where('type_id', $gift->id)->count();
			$gift->is_receive = $receive ? true : false;

			return $gift;
		}

		return null;
	}

}
