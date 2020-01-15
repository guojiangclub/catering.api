<?php

/*
 * This file is part of ibrand/catering-backend.
 *
 * (c) iBrand <https://www.ibrand.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GuoJiangClub\EC\Catering\Backend\Http\Controllers;

use Carbon\Carbon;
use DB;
use GuoJiangClub\EC\Catering\Backend\Models\User;
use Encore\Admin\Facades\Admin as LaravelAdmin;
use Encore\Admin\Layout\Content;
use GuoJiangClub\Catering\Component\Order\Models\Order;
use GuoJiangClub\Catering\Component\Balance\Model\BalanceOrder;
use GuoJiangClub\Catering\Component\Payment\Models\Payment;
use GuoJiangClub\Catering\Backend\Models\Coupon\Coupon;
use iBrand\Wechat\Backend\Facades\FanService;

class DataController extends Controller
{
	public function index()
	{
		$orderTotal       = 0;
		$adjustmentsTotal = 0;
		$rechargeTotal    = 0;
		$balanceTotal     = 0;

		$orders = Order::whereIn('status', [2, 3, 4, 5])->get(['total', 'adjustments_total']);
		if ($orders->count() > 0) {
			$orderTotal       = $orders->sum('total') / 100;
			$adjustmentsTotal = $orders->sum('adjustments_total') / 100;
		}

		$balanceOrders = BalanceOrder::where('pay_status', 1)->whereNotNull('pay_time')->get(['amount', 'pay_amount']);
		if ($balanceOrders->count() > 0) {
			$rechargeTotal = $balanceOrders->sum('amount') / 100;
		}

		$payments = Payment::where('status', Payment::STATUS_COMPLETED)->where('channel', 'balance')->get(['amount']);
		if ($payments->count() > 0) {
			$balanceTotal = $payments->sum('amount') / 100;
		}

		$userTotal = 0;
		$users     = User::where('status', 1)->get();
		if ($users->count() > 0) {
			$userTotal = $users->count();
		}

		$couponTotal = 0;
		$coupons     = Coupon::whereNotNull('used_at')->get();
		if ($coupons->count() > 0) {
			$couponTotal = $coupons->count();
		}

		$fans      = FanService::getLists();
		$fansTotal = 0;
		if (isset($fans->data->openid) && count($fans->data->openid) > 0) {
			$fansTotal = $fans->total;
		}

		return LaravelAdmin::content(function (Content $content) use ($orderTotal, $adjustmentsTotal, $rechargeTotal, $balanceTotal, $userTotal, $fansTotal, $couponTotal) {
			$content->header('会员概览');

			$content->breadcrumb(
				['text' => '会员概览', 'no-pjax' => 1, 'left-menu-active' => '会员概览']
			);

			$content->body(view('catering-backend::data.index', compact('orderTotal', 'rechargeTotal', 'adjustmentsTotal', 'balanceTotal', 'userTotal', 'fansTotal', 'couponTotal')));
		});
	}

	public function getMonthData()
	{
		/*最近12个月数据*/
		$date            = array_reverse(getLastMonthArea(Carbon::now()->year, Carbon::now()->month, 12, 1));
		$monthList       = [];
		$monthTotal      = [];
		$checkMonthArray = [];
		foreach ($date as $key => $item) {
			$monthList[]       = $item['currentMonth'];
			$checkMonthArray[] = $item['currentMonth'];
		}

		/*最近12个月用户数据*/
		$startMonth     = $date[0]['startMonth'];
		$endMonth       = $date[11]['endMonth'];
		$monthUserData  = DB::table(config('ibrand.app.database.prefix', 'ibrand_') . 'user')->select(DB::raw('substr(created_at, 1, 7) as yearmonth, COUNT(*) as total'))->whereBetween('created_at', [$startMonth, $endMonth])->groupBy('yearmonth')->get();
		$monthUserTotal = [];
		foreach ($checkMonthArray as $key => $item) {
			$filter = $monthUserData->filter(function ($value) use ($item) {
				return $value->yearmonth == $item;
			});

			if (count($filter) == 0) {
				$monthUserTotal[] = 0;
			} else {
				$monthUserTotal[] = $filter->first()->total;
			}
		}

		/*最近12个月公众号数据*/
		$monthUserBindData  = DB::table(config('ibrand.app.database.prefix', 'ibrand_') . 'user_bind')->select(DB::raw('substr(created_at, 1, 7) as yearmonth, COUNT(*) as total'))->where('type', 'wechat')->whereBetween('created_at', [$startMonth, $endMonth])->groupBy('yearmonth')->get();
		$monthUserBindTotal = [];
		foreach ($checkMonthArray as $key => $item) {
			$filter = $monthUserBindData->filter(function ($value) use ($item) {
				return $value->yearmonth == $item;
			});

			if (count($filter) == 0) {
				$monthUserBindTotal[] = 0;
			} else {
				$monthUserBindTotal[] = $filter->first()->total;
			}
		}

		/*最近30天数据*/
		$daysData = getLastDayArea();
		$dayList  = $daysData[0];
		$dayTime  = $daysData[1];
		$startDay = $dayTime[0] . ' 00:00:00';
		$endDay   = $dayTime[29] . ' 23:59:59';

		/*最近30天用户数据*/
		$daysUserData  = DB::table(config('ibrand.app.database.prefix', 'ibrand_') . 'user')->select(DB::raw('substr(created_at, 1, 10) as monthDay, COUNT(*) as total'))->whereBetween('created_at', [$startDay, $endDay])->groupBy('monthDay')->get();
		$daysUserTotal = [];
		foreach ($dayTime as $item) {
			$filter = $daysUserData->filter(function ($value) use ($item) {
				return $value->monthDay == $item;
			});
			if (count($filter) == 0) {
				$daysUserTotal[] = 0;
			} else {
				$daysUserTotal[] = $filter->first()->total;
			}
		}

		/*最近30天公众号数据*/
		$daysUserBindData  = DB::table(config('ibrand.app.database.prefix', 'ibrand_') . 'user_bind')->select(DB::raw('substr(created_at, 1, 10) as monthDay, COUNT(*) as total'))->where('type', 'wechat')->whereBetween('created_at', [$startDay, $endDay])->groupBy('monthDay')->get();
		$daysUserBindTotal = [];
		foreach ($dayTime as $item) {
			$filter = $daysUserBindData->filter(function ($value) use ($item) {
				return $value->monthDay == $item;
			});
			if (count($filter) == 0) {
				$daysUserBindTotal[] = 0;
			} else {
				$daysUserBindTotal[] = $filter->first()->total;
			}
		}

		return $this->ajaxJson(true, ['monthTotal' => $monthTotal, 'monthList' => $monthList, 'dayList' => $dayList, 'monthUserTotal' => $monthUserTotal, 'daysUserTotal' => $daysUserTotal, 'monthUserBindTotal' => $monthUserBindTotal, 'daysUserBindTotal' => $daysUserBindTotal]);
	}
}
