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
use GuoJiangClub\Catering\Component\Recharge\Models\RechargeRule;
use GuoJiangClub\Catering\Component\Recharge\Repositories\BalanceOrderRepository;
use GuoJiangClub\Catering\Component\Recharge\Repositories\GiftCouponRepository;
use GuoJiangClub\Catering\Component\Recharge\Repositories\RechargeRuleRepository;
use GuoJiangClub\Catering\Backend\Repositories\DiscountRepository;
use Encore\Admin\Facades\Admin as LaravelAdmin;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use Validator;

class RechargeController extends Controller
{
	protected $discountRepository;

	protected $rechargeRuleRepository;

	protected $giftCouponRepository;

	protected $balanceOrderRepository;

	public function __construct(
		DiscountRepository $discountRepository,
		RechargeRuleRepository $rechargeRuleRepository,
		GiftCouponRepository $giftCouponRepository,
		BalanceOrderRepository $balanceOrderRepository
	)
	{
		$this->discountRepository     = $discountRepository;
		$this->rechargeRuleRepository = $rechargeRuleRepository;
		$this->giftCouponRepository   = $giftCouponRepository;
		$this->balanceOrderRepository = $balanceOrderRepository;
	}

	public function index()
	{
		$condition          = $this->getCondition();
		$coupons            = [];
		$where              = $condition[0];
		$orWhere            = $condition[1];
		$where['status']    = 1;
		$where['starts_at'] = ['<=', Carbon::now()];
		$where['ends_at']   = ['>', Carbon::now()];
		$coupons            = $this->discountRepository->getDiscountList($where, $orWhere);
		if (count($coupons) > 0) {
			$coupons = $coupons->pluck('id')->toArray();
		}

		$name = request('name');

		$lists = $this->rechargeRuleRepository->getAll($name);

		return LaravelAdmin::content(function (Content $content) use ($lists, $coupons) {
			$content->header('储值管理');

			$content->breadcrumb(
				['text' => '储值管理', 'url' => 'member/recharge', 'no-pjax' => 1, 'left-menu-active' => '储值管理']
			);

			$content->body(view('catering-backend::recharge.index', compact('lists', 'coupons')));
		});
	}

	public function create()
	{
		return LaravelAdmin::content(function (Content $content) {
			$content->header('储值管理');

			$content->breadcrumb(
				['text' => '储值管理', 'url' => 'member/recharge', 'no-pjax' => 1],
				['text' => '创建储值规则', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '储值管理']
			);

			$content->body(view('catering-backend::recharge.create'));
		});
	}

	public function edit($id)
	{
		$recharge = $this->rechargeRuleRepository->getRechargeByIDStatusOff($id);
		$coupon   = [];
		if (count($recharge->gift) > 0) {
			foreach ($recharge->gift as $k => $item) {
				$coupon[$k] = $item->coupon->id;
			}
		}
		$coupon = json_encode($coupon, true);

		return LaravelAdmin::content(function (Content $content) use ($recharge, $coupon) {
			$content->header('储值管理');

			$content->breadcrumb(
				['text' => '储值管理', 'url' => 'member/recharge', 'no-pjax' => 1],
				['text' => '编辑储值规则', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '储值管理']
			);

			$content->body(view('catering-backend::recharge.edit', compact('recharge', 'coupon')));
		});
	}

	public function update(Request $request)
	{
		$input = $request->except(['_token','file']);
		$rules = [
			'name'           => 'required',
			'payment_amount' => 'required|numeric',
			'amount'         => 'required|numeric',
			'point'          => 'integer|min:0',
		];

		$message = [
			"required" => ":attribute 不能为空",
			"numeric"  => ":attribute 必须为数字",
			"min"      => ":attribute 必须为大于0",
		];

		$attributes = [
			'name'           => '储值规则名称',
			'payment_amount' => '实付金额',
			'amount'         => '到账金额',
			'point'          => '赠送积分',
		];

		$validator = Validator::make($input, $rules, $message, $attributes);
		if ($validator->fails()) {
			$warnings     = $validator->messages();
			$show_warning = $warnings->first();

			return $this->ajaxJson(false, [], 404, $show_warning);
		}

		if ($input['amount'] <= 0 || $input['payment_amount'] <= 0) {
			return $this->ajaxJson(false, [], 404, '实付金额、到账金额必须大于0');
		}

		if ($input['amount'] < $input['payment_amount']) {
			return $this->ajaxJson(false, [], 404, '到账金额 必须大于等于 实付金额');
		}

		$input['amount']         = intval(100 * $input['amount']);
		$input['payment_amount'] = intval(100 * $input['payment_amount']);
		$input['point']          = !empty($input['point']) ? $input['point'] : 0;
		$input['open_point']     = $input['point'] > 0 ? 1 : 0;

		try {
			DB::beginTransaction();
			$coupon_id = $input['coupon'];
			unset($input['coupon']);
			$res = $this->rechargeRuleRepository->update($input, $input['id']);
			if ($input['open_coupon']) {
				$gift                = $this->giftCouponRepository->findWhere(['type' => 'gift_recharge', 'type_id' => $input['id']])->first();
				$coupon['type']      = $input['type'];
				$coupon['type_id']   = $res->id;
				$coupon['coupon_id'] = intval($coupon_id);
				$coupon['num']       = 1;
				$coupon['status']    = 1;
				if (isset($gift->id)) {
					$this->giftCouponRepository->update($coupon, $gift->id);
				} else {
					$this->giftCouponRepository->create($coupon);
				}
			}
			DB::commit();

			return $this->ajaxJson();
		} catch (\Exception $exception) {
			DB::rollBack();
			\Log::info($exception->getMessage() . $exception->getTraceAsString());

			return $this->ajaxJson(false, [], 400, '');
		}
	}

	public function store(Request $request)
	{
		$input = $request->except(['_token','file']);
		$rules = [
			'name'           => 'required',
			'payment_amount' => 'required|numeric',
			'amount'         => 'required|numeric',
			'point'          => 'integer|min:0',
		];

		$message = [
			"required" => ":attribute 不能为空",
			"numeric"  => ":attribute 必须为数字",
			"min"      => ":attribute 必须为大于0",
		];

		$attributes = [
			'name'           => '储值规则名称',
			'payment_amount' => '实付金额',
			'amount'         => '到账金额',
			'point'          => '赠送积分',
		];

		$validator = Validator::make($input, $rules, $message, $attributes);
		if ($validator->fails()) {
			$warnings     = $validator->messages();
			$show_warning = $warnings->first();

			return $this->ajaxJson(false, [], 404, $show_warning);
		}

		if ($input['amount'] <= 0 || $input['payment_amount'] <= 0) {
			return $this->ajaxJson(false, [], 404, '实付金额、到账金额必须大于0');
		}

		if ($input['amount'] < $input['payment_amount']) {
			return $this->ajaxJson(false, [], 404, '到账金额 必须大于等于 实付金额');
		}

		$input['amount']         = intval(100 * $input['amount']);
		$input['payment_amount'] = intval(100 * $input['payment_amount']);
		$input['point']          = !empty($input['point']) ? $input['point'] : 0;
		$input['open_point']     = $input['point'] > 0 ? 1 : 0;

		try {
			DB::beginTransaction();
			$coupon_id = $input['coupon'];
			unset($input['coupon']);
			$res = $this->rechargeRuleRepository->create($input);
			if ($input['open_coupon']) {
				$coupon['type']      = $input['type'];
				$coupon['type_id']   = $res->id;
				$coupon['coupon_id'] = intval($coupon_id);
				$coupon['num']       = 1;
				$coupon['status']    = 1;
				$this->giftCouponRepository->create($coupon);
			}
			DB::commit();
		} catch (\Exception $exception) {
			DB::rollBack();
			\Log::info($exception->getMessage() . $exception->getTraceAsString());

			return $this->ajaxJson(false, [], 400, '');
		}

		return $this->ajaxJson(true, [], 200, '');
	}

	public function coupon_api()
	{
		$condition = $this->getCondition();
		$where     = $condition[0];
		$orWhere   = $condition[1];
		$coupons   = $this->discountRepository->getDiscountList($where, $orWhere);

		return $this->ajaxJson(true, $coupons, 200, '');
	}

	/**
	 * 获取筛选条件.
	 *
	 * @return array
	 */
	private function getCondition()
	{
		$where['coupon_based'] = 1;
		$orWhere               = [];
		$status                = request('status');
		if ('nstart' == $status) {
			$where['status']    = 1;
			$where['starts_at'] = ['>', Carbon::now()];
		}

		if ('ing' == $status) {
			$where['status']    = 1;
			$where['starts_at'] = ['<=', Carbon::now()];
			$where['ends_at']   = ['>', Carbon::now()];
		}

		if ('end' == $status) {
			$where['ends_at'] = ['<', Carbon::now()];

			$orWhere['coupon_based'] = 1;
			$orWhere['status']       = 0;
		}

		if ('' != request('title')) {
			$where['title'] = ['like', '%' . request('title') . '%'];
		}

		$where['type'] = ['<>', 1];

		return [$where, $orWhere];
	}

	public function destroy($id)
	{
		$this->rechargeRuleRepository->delete($id);

		return $this->ajaxJson();
	}

	public function toggleStatus()
	{
		$status = request('status');
		$id     = request('aid');
		$item   = $this->rechargeRuleRepository->findWhere(['id' => $id])->first();
		if ($item) {
			$user         = RechargeRule::find($id);
			$user->status = $status;
			$user->save();

			return $this->ajaxJson(true, [], 200, '');
		}

		return $this->ajaxJson(false, [], 400, '操作失败');
	}

	public function log()
	{
		$where               = [];
		$id                  = 0;
		$where['pay_status'] = 1;
		$condition           = $this->getListWhere();
		$where               = $condition[1];
		$time                = $condition[0];
		$lists               = $this->balanceOrderRepository->getLists($where, 20, $time);

		if (isset($where['recharge_rule_id'])) {
			$id = $where['recharge_rule_id'];
		}
//        $data=$lists=$this->balanceOrderRepository->getLists($where,0,$time);
		//return view('catering-backend::recharge.log', compact('lists', 'id'));

		return LaravelAdmin::content(function (Content $content) use ($lists, $id) {
			$content->header('储值管理');

			$content->breadcrumb(
				['text' => '储值管理', 'url' => 'member/recharge', 'no-pjax' => 1],
				['text' => '储值记录', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '储值管理']
			);

			$content->body(view('catering-backend::recharge.log', compact('lists', 'id')));
		});
	}

	private function getListWhere()
	{
		$time  = [];
		$where = [];

		if ($id = request('id')) {
			$where['recharge_rule_id'] = $id;
		}

		if ($order_no = request('order_no')) {
			$where['order_no'] = ['like', '%' . request('order_no') . '%'];
		}

		if (!empty(request('mobile'))) {
			$where['mobile'] = ['like', '%' . request('mobile') . '%'];
		}

		if (!empty(request('etime')) && !empty(request('stime'))) {
			$where['pay_time'] = ['<=', request('etime')];
			$time['pay_time']  = ['>=', request('stime')];
		}

		if (!empty(request('etime'))) {
			$time['pay_time'] = ['<=', request('etime')];
		}

		if (!empty(request('stime'))) {
			$time['pay_time'] = ['>=', request('stime')];
		}

		return [$time, $where];
	}
}
