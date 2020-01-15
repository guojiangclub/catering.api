<?php

namespace GuoJiangClub\Catering\Backend\Service;

use GuoJiangClub\Catering\Backend\Models\Coupon\Action;
use GuoJiangClub\Catering\Backend\Models\Coupon\Discount;
use GuoJiangClub\Catering\Backend\Models\Coupon\Rule;
use GuoJiangClub\Catering\Core\Auth\User;

class DiscountService
{
	/**
	 * 保存活动/优惠券
	 *
	 * @param $base
	 * @param $action
	 * @param $rules
	 * @param $coupon_base
	 *
	 * @return bool|static
	 */
	public function saveData($base, $action, $rules, $coupon_base)
	{
		if ($id = request('id')) { //修改

			$discount = Discount::find($id);
			$base     = array_filter($base);
			$discount->fill($base);
			$discount->save();

			//action
			if ($actionData = Action::find(request('action_id'))) {
				if ($action['configuration']) {
					$actionData->fill($action);
					$actionData->save();
				} else {
					$actionData->delete();
				}
			} elseif ($action['configuration']) {
				$action['discount_id'] = $discount->id;
				Action::create($action);
			}

			//point action
			if ($pointAction = Action::find(request('point_action_id'))) {
				if (request('point-action')['configuration']) {
					$pointAction->fill(request('point-action'));
					$pointAction->save();
				} else {
					$pointAction->delete();
				}
			} elseif (request('point-action')['configuration']) {
				$addPointAction                = request('point-action');
				$addPointAction['discount_id'] = $discount->id;
				Action::create($addPointAction);
			}

			//delete rules
			$discount->rules()->delete();
		} else {
			$base['code']         = date('YmdHis');
			$base['coupon_based'] = $coupon_base;
			$base                 = array_filter($base);

			$discount = Discount::create($base);

			//action
			if ($action['configuration']) {
				$action['discount_id'] = $discount->id;
				Action::create($action);
			}

			if (request('point-action')['configuration']) {
				$addPointAction                = request('point-action');
				$addPointAction['discount_id'] = $discount->id;
				Action::create($addPointAction);
			}
		}

		//rules
		$filterRules = $this->filterDiscountRules($rules);
		if (count($filterRules) == 0) {
			return false;
		}

		foreach ($filterRules as $key => $val) {
			$rulesData                  = [];
			$rulesData['discount_id']   = $discount->id;
			$rulesData['type']          = $val['type'];
			$rulesData['configuration'] = $val['value'];

			Rule::create($rulesData);
		}

		return $discount;
	}

	public function searchAllCouponsHistoryExcel($coupons = [])
	{
		$date = [];
		if (count($coupons) > 0) {
			$i = 0;
			foreach ($coupons as $coupon) {
				$date[$i][] = $coupon->created_at;
				$date[$i][] = $coupon->used_at;
				$order      = $coupon->getOrder();
				$date[$i][] = $order ? $order->shop->name : '';
				$date[$i][] = Discount::find($coupon->discount_id)->title;
				$date[$i][] = $coupon->code;
				$date[$i][] = $order ? $order->order_no : '';
				$date[$i][] = $order ? number_format($order->amount / 100, 2, '.', '') : '';
				$date[$i][] = $order ? number_format($order->adjustment->amount / 100, 2, '.', '') : '';
				$date[$i][] = $order ? number_format($order->total_amount / 100, 2, '.', '') : '';
				if ($order) {
					$date[$i][] = '已支付';
				} elseif ($coupon->market_manager_id && $coupon->coupon_use_code) {
					$date[$i][] = '线下已核销';
				} else {
					$date[$i][] = '未支付';
				}

				$date[$i][] = User::find($coupon->user_id)->mobile;
				$i++;
			}
		}

		return $date;
	}

	public function couponsGetDataExcel($coupons = [])
	{
		$date = [];
		if (count($coupons) > 0) {
			$i = 0;
			foreach ($coupons as $coupon) {
				foreach ($coupon->created_at as $value) {
					$date[$i][] = basename($value, "." . substr(strchr($value, '.'), 1));
				}
				unset($date[$i][2]);
				unset($date[$i][1]);

				$date[$i][] = $coupon->code;

				$user       = User::find($coupon->user_id);
				$date[$i][] = $user ? $user->mobile : '';
				$date[$i][] = $coupon->used_at ? '已使用' : '未使用';
				$date[$i][] = $coupon->used_at;
				$i++;
			}
		}

		return $date;
	}

	/**
	 * 过滤活动规则
	 *
	 * @param $data
	 *
	 * @return array
	 */
	public function filterDiscountRules($data)
	{

		foreach ($data as $key => $val) {
			if (!isset($val['type'])) {
				unset($data[$key]);
				continue;
			}

			if (isset($val['type']) AND !is_array($val) AND empty($val)) {
				unset($data[$key]);
				continue;
			}

			if ($val['type'] == 'contains_product' AND empty($val['value']['sku']) AND empty($val['value']['spu'])) {
				unset($data[$key]);
				continue;
			}

			if ($val['type'] == 'contains_category' AND (!isset($val['value']['items']) || count($val['value']['items']) == 0)) {
				unset($data[$key]);
				continue;
			}

			if ($val['type'] == 'contains_shops' AND count($val['value']['shop_id']) == 0) {
				unset($data[$key]);
				continue;
			}

			if ($val['type'] == 'contains_market_shop' AND count($val['value']['shop_ids']) == 0) {
				unset($data[$key]);
				continue;
			}

			if ($val['type'] == 'contains_market' AND count($val['value']['market_ids']) == 0) {
				unset($data[$key]);
				continue;
			}
		}

		if (count($data) == 0) {
			//return ['status' => false, 'message' => '请至少设置一种规则'];
			return [];
		}

		return $data;
	}
}