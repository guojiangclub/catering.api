<?php

namespace GuoJiangClub\Catering\Backend\Http\Controllers;

use Carbon\Carbon;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use iBrand\Backend\Http\Controllers\Controller;
use GuoJiangClub\Catering\Backend\Models\Coupon\Discount;
use GuoJiangClub\Catering\Backend\Models\Coupon\Coupon;
use ElementVip\Store\Backend\Model\User;
use GuoJiangClub\Catering\Backend\Repositories\DiscountRepository;
use GuoJiangClub\Catering\Backend\Repositories\CouponRepository;
use Illuminate\Http\Request;
use GuoJiangClub\Catering\Backend\Service\DiscountService;
use DB;
use Validator;

class CouponController extends Controller
{
	protected $discountRepository;
	protected $discountService;
	protected $couponRepository;
	protected $cache;

	public function __construct(DiscountRepository $discountRepository,
	                            DiscountService $discountService,
	                            CouponRepository $couponRepository)
	{
		$this->discountRepository = $discountRepository;
		$this->discountService    = $discountService;
		$this->couponRepository   = $couponRepository;
		$this->cache              = cache();
	}

	public function index()
	{
		$condition = $this->getCondition();
		$where     = $condition[0];
		$orWhere   = $condition[1];

		$coupons = $this->discountRepository->getDiscountList($where, $orWhere, 10);

		return Admin::content(function (Content $content) use ($coupons) {

			$content->header('优惠券列表');

			$content->breadcrumb(
				['text' => '优惠券列表', 'no-pjax' => 1, 'left-menu-active' => '优惠券']
			);

			$content->body(view('backend-shitang::coupon.index', compact('coupons')));
		});
	}

	public function getCouponsList()
	{
		$id = request('id');

		$coupons = $this->discountRepository->scopeQuery(function ($query) {
			return $query->where('status', 1)->where('coupon_based', 1)->where(function ($query) {
				$query->whereNull('starts_at')
					->orWhere(function ($query) {
						$query->where('starts_at', '<', Carbon::now());
					});
			})->where(function ($query) {
				$query->whereNull('ends_at')
					->orWhere(function ($query) {
						$query->where('ends_at', '>', Carbon::now());
					});
			});
		})->get();

		return view('backend-shitang::setting.includes.coupons', compact('id', 'coupons'));
	}

	/**
	 * 获取筛选条件
	 *
	 * @return array
	 */
	private function getCondition()
	{
		$where['coupon_based'] = 1;
		$orWhere               = [];
		$status                = request('status');
		if ($status == 'nstart') {
			$where['status']    = 1;
			$where['starts_at'] = ['>', Carbon::now()];
		}

		if ($status == 'ing') {
			$where['status']    = 1;
			$where['starts_at'] = ['<=', Carbon::now()];
			$where['ends_at']   = ['>', Carbon::now()];
		}

		if ($status == 'end') {
			$where['ends_at'] = ['<', Carbon::now()];

			$orWhere['coupon_based'] = 1;
			$orWhere['status']       = 0;
		}

		if (request('title') != '') {
			$where['title'] = ['like', '%' . request('title') . '%'];
		}

		return [$where, $orWhere];
	}

	public function create()
	{
		$discount = new Discount();

		return Admin::content(function (Content $content) use ($discount) {

			$content->header('新增优惠券');

			$content->breadcrumb(
				['text' => '新增优惠券', 'no-pjax' => 1, 'left-menu-active' => '优惠券']
			);

			$content->body(view('backend-shitang::coupon.create', compact('discount')));
		});
	}

	public function edit($id)
	{
		$discount = Discount::find($id);

		return Admin::content(function (Content $content) use ($discount) {

			$content->header('编辑优惠券');

			$content->breadcrumb(
				['text' => '编辑优惠券', 'no-pjax' => 1, 'left-menu-active' => '优惠券']
			);

			$content->body(view('backend-shitang::coupon.edit', compact('discount')));
		});
	}

	public function store(Request $request)
	{
		$base         = $request->input('base');
		$rules        = $request->input('rules');
		$action       = $request->input('action');
		$point_action = $request->input('point-action');

		if (!$base['usestart_at']) {
			unset($base['usestart_at']);
		}

		$validator = $this->validationForm();
		if ($validator->fails()) {
			$warnings     = $validator->messages();
			$show_warning = $warnings->first();

			return $this->ajaxJson(false, [], 404, $show_warning);
		}

		if (!$action['configuration'] AND !$point_action['configuration']) {
			return $this->ajaxJson(false, [], 404, '请至少设置一种优惠动作');
		}

		try {
			DB::beginTransaction();

			if (!$discount = $this->discountService->saveData($base, $action, $rules, 1)) {
				return $this->ajaxJson(false, [], 404, '请至少设置一种规则');
			}

			DB::commit();

			return $this->ajaxJson(true, [], 0, '');
		} catch (\Exception $exception) {
			DB::rollBack();
			\Log::info($exception->getTraceAsString());
			\Log::info($exception->getMessage());

			return $this->ajaxJson(false, [], 404, '保存失败');
		}
	}

	protected function validationForm()
	{
		$rules = [
			'base.title'       => 'required',
			'base.intro'       => 'required',
			'base.usage_limit' => 'required | integer',
			'base.starts_at'   => 'required | date',
			'base.ends_at'     => 'required | date | after:base.starts_at',
		];

		$message = [
			"required"             => ":attribute 不能为空",
			"base.ends_at.after"   => ':attribute 不能早于领取开始时间',
			"base.useend_at.after" => ':attribute 不能早于领取截止时间',
			"integer"              => ':attribute 必须是整数',
			"unique"               => ':attribute 已经存在',
		];

		$attributes = [
			"base.title"       => '优惠券名称',
			"base.label"       => '规则',
			"base.intro"       => '使用说明',
			"base.usage_limit" => '发放总量',
			"base.starts_at"   => '开始时间',
			"base.ends_at"     => '领取截止时间',
			"base.useend_at"   => '使用截止时间',
		];

		$validator = Validator::make(
			request()->all(),
			$rules,
			$message,
			$attributes
		);

		$validator->sometimes('base.useend_at', 'required|date|after:base.ends_at', function ($input) {
			return ($input->base['useend_at'] < $input->base['ends_at']) AND !$input->base['effective_days'];
		});

		return $validator;
	}

	/**
	 * 使用记录
	 *
	 * @return mixed
	 */
	public function useRecord()
	{
		$condition = $this->usedCondition();
		$where     = $condition[1];
		$time      = $condition[0];
		$id        = request('id');

		$coupons = $this->couponRepository->getCouponsHistoryPaginated($where, 20, $time);

		return Admin::content(function (Content $content) use ($coupons, $id) {

			$content->header('查看使用记录');

			$content->breadcrumb(
				['text' => '查看使用记录', 'no-pjax' => 1, 'left-menu-active' => '优惠券']
			);

			$content->body(view('backend-shitang::coupon.use_record', compact('coupons', 'id')));
		});
	}

	/**
	 * 优惠券使用筛选条件
	 *
	 * @return array
	 */
	private function usedCondition()
	{
		$time  = [];
		$where = [];

		$where['user_id'] = ['>', 0];

		if ($id = request('id')) {
			$where['discount_id'] = $id;
		}

		if (!empty(request('value'))) {
			$where[request('field')] = ['like', '%' . request('value') . '%'];
		}

		if (!empty(request('etime')) && !empty(request('stime'))) {
			$where['used_at'] = ['<=', request('etime')];
			$time['used_at']  = ['>=', request('stime')];
		}

		if (!empty(request('etime'))) {
			$time['used_at'] = ['<=', request('etime')];
		}

		if (!empty(request('stime'))) {
			$time['used_at'] = ['>=', request('stime')];
		}

		return [$time, $where];
	}

	/**
	 * 领取记录
	 *
	 * @return mixed
	 */
	public function showCoupons()
	{
		$condition = $this->getCouponCondition();
		$where     = $condition[1];
		$time      = $condition[0];
		$id        = request('id');

		$coupons = $this->couponRepository->getCouponsPaginated($where, 15, $time);

		return Admin::content(function (Content $content) use ($coupons, $id) {

			$content->header('查看领取记录');

			$content->breadcrumb(
				['text' => '查看领取记录', 'no-pjax' => 1, 'left-menu-active' => '优惠券']
			);

			$content->body(view('backend-shitang::coupon.show', compact('coupons', 'id')));
		});
	}

	private function getCouponCondition()
	{
		$time  = [];
		$where = [];

		if ($id = request('id')) {
			$where['discount_id'] = $id;
		}

		if (!empty(request('value'))) {
			$where['mobile'] = ['like', '%' . request('value') . '%'];
		}

		if (!empty(request('etime')) && !empty(request('stime'))) {
			$where['created_at'] = ['<=', request('etime')];
			$time['created_at']  = ['>=', request('stime')];
		}

		if (!empty(request('etime'))) {
			$time['created_at'] = ['<=', request('etime')];
		}

		if (!empty(request('stime'))) {
			$time['created_at'] = ['>=', request('stime')];
		}

		return [$time, $where];
	}

	/**
	 * 发送优惠券页面
	 *
	 * @return mixed
	 */
	public function sendCoupon()
	{
		$id     = request('id');
		$coupon = $this->discountRepository->find($id);

		return view('backend-shitang::coupon.send', compact('coupon', 'id'));
	}

	/**
	 * 筛选用户
	 * */
	public function filterUser()
	{
		return view('backend-shitang::coupon.includes.send_user');
	}

	/**
	 * 搜索用户
	 *
	 * @param Request $request
	 *
	 * @return mixed
	 */
	public function getUsers(Request $request)
	{
		$ids = explode(',', $request->input('ids'));
		if (!empty(request('value'))) {
			$user = User::where('name', 'like', '%' . request('value') . '%')
				->orWhere('mobile', 'like', '%' . request('value') . '%')
				->orWhere('nick_name', 'like', '%' . request('value') . '%')
				->paginate(15)->toArray();
		} else {
			$user = User::paginate(15)->toArray();
		}
		$user['ids'] = $ids;

		return $this->ajaxJson(true, $user);
	}

	/**
	 * 获取已选择的用户
	 *
	 * @param Request $request
	 *
	 * @return mixed
	 */
	public function getSelectedUsersByID(Request $request)
	{
		$ids  = explode(',', $request->input('ids'));
		$user = User::whereIn('id', $ids)->get();

		return $this->ajaxJson(true, $user);
	}

	/**
	 * 给用户发送优惠券
	 */
	public function sendAction()
	{
		$user_ids    = explode(',', request('user_ids'));
		$discount_id = request('discount_id');
		$discount    = Discount::find($discount_id);

		if ($discount->status == 0 OR ($discount->status == 1 AND ($discount->ends_at < Carbon::now() OR $discount->starts_at > Carbon::now()))) {
			return $this->ajaxJson(false, [], 404, '无效的优惠券');
		}

		if (!request('user_ids')) {
			return $this->ajaxJson(false, [], 404, '未选择任何用户');
		}

		if ($discount->usage_limit < count($user_ids)) {
			return $this->ajaxJson(false, [], 404, '该优惠券剩余发行量小于发送的用户总数');
		}

		try {
			DB::beginTransaction();
			foreach ($user_ids as $item) {
				if (!$this->couponRepository->userGetCoupons($item, $discount_id)) {
					return $this->ajaxJson(false, [], 404, '发送失败');
				}
			}
			DB::commit();

			return $this->ajaxJson(true, ['count' => count($user_ids)], 0, '');
		} catch (\Exception $exception) {
			DB::rollBack();
			\Log::info($exception);

			return $this->ajaxJson(false, [], 404, '发送失败');
		}
	}

	/**
	 * 生成优惠码modal
	 *
	 * @return mixed
	 */
	public function couponCode()
	{
		$discount_id = request('discount_id');
		$discount    = Discount::find($discount_id);

		$countCoupon = Coupon::where('user_id', 0)->where('discount_id', $discount_id)->count();

		$range = $discount->usage_limit;
		if ($range >= 1000) {
			$limit = 1000;
		} elseif ($range < 1000 AND $range > 0) {
			$limit = $range;
		} else {
			$limit = 0;
		}

		return view('backend-shitang::coupon.includes.coupon_code_modal', compact('limit', 'discount_id'));
	}

	/**
	 * 生成优惠码
	 *
	 * @return mixed
	 */
	public function createCouponCode()
	{
		$number      = request('number');
		$discount_id = request('discount_id');
		$discount    = Discount::find($discount_id);
		$range       = $discount->usage_limit;

		if ($number > 1000 OR $number >= $range) {
			return $this->ajaxJson(false, [], '400', '生成数量超过限制');
		}

		$data = [];
		for ($i = 1; $i <= $number; $i++) {
			$code     = $this->buildCode();
			$codeData = ['user_id' => 0, 'code' => $code];
			array_push($data, $codeData);
		}

		$discount->coupons()->createMany($data);
		$discount->decrement('usage_limit', $number);

		return $this->ajaxJson(true);
	}

	private function buildCode()
	{
		$code = build_order_no('CT');
		if (Coupon::where('code', $code)->first()) {
			return $this->buildCode();
		}

		return $code;
	}

	/**
	 * 导出生成的优惠码
	 *
	 * @return mixed
	 */
	public function getExportData()
	{
		$page  = request('page') ? request('page') : 1;
		$limit = request('limit') ? request('limit') : 50;
		$type  = request('type');

		$data     = $this->couponRepository->getExportDataPaginate(request('discount_id'), $limit);
		$lastPage = $data['lastPage'];
		$coupons  = $data['data'];

		if ($page == 1) {
			session(['export_market_coupon_code_cache' => generate_export_cache_name('export_market_coupon_code_cache_')]);
		}
		$cacheName = session('export_market_coupon_code_cache');

		if ($this->cache->has($cacheName)) {
			$cacheData = $this->cache->get($cacheName);
			$this->cache->put($cacheName, array_merge($cacheData, $coupons), 300);
		} else {
			$this->cache->put($cacheName, $coupons, 300);
		}

		if ($page == $lastPage) {
			$title = ['优惠码'];

			return $this->ajaxJson(true, ['status' => 'done', 'url' => '', 'type' => $type, 'title' => $title, 'cache' => $cacheName, 'prefix' => 'coupon_code_data_']);
		} else {
			$url_bit = route('admin.promotion.coupon.getExportData', array_merge(['page' => $page + 1, 'limit' => $limit], request()->except('page', 'limit')));

			return $this->ajaxJson(true, ['status' => 'goon', 'url' => $url_bit, 'page' => $page, 'totalPage' => $lastPage]);
		}
	}

	/**
	 * 导出使用记录
	 *
	 * @return mixed
	 */
	public function getCouponsUsedExportData()
	{
		$page  = request('page') ? request('page') : 1;
		$limit = request('limit') ? request('limit') : 20;
		$type  = request('type');

		$condition = $this->usedCondition();
		$where     = $condition[1];
		$time      = $condition[0];

		$coupons  = $this->couponRepository->getCouponsHistoryPaginated($where, $limit, $time);
		$lastPage = $coupons->lastPage();
		$coupons  = $this->discountService->searchAllCouponsHistoryExcel($coupons);

		if ($page == 1) {
			session(['export_market_coupon_used_cache' => generate_export_cache_name('export_market_coupon_used_cache_')]);
		}
		$cacheName = session('export_market_coupon_used_cache');

		if ($this->cache->has($cacheName)) {
			$cacheData = $this->cache->get($cacheName);
			$this->cache->put($cacheName, array_merge($cacheData, $coupons), 300);
		} else {
			$this->cache->put($cacheName, $coupons, 300);
		}

		if ($page == $lastPage) {
			$title = ['领取时间', '核销日期', '核销门店', '优惠券名', '优惠券码', '订单编号', '支付金额', '优惠金额', '订单总金额', '订单状态', '用户名'];

			return $this->ajaxJson(true, ['status' => 'done', 'url' => '', 'type' => $type, 'title' => $title, 'cache' => $cacheName, 'prefix' => 'coupon_used_data_']);
		} else {
			$url_bit = route('admin.shitang.coupon.getCouponsUsedExportData', array_merge(['page' => $page + 1, 'limit' => $limit], request()->except('page', 'limit')));

			return $this->ajaxJson(true, ['status' => 'goon', 'url' => $url_bit, 'page' => $page, 'totalPage' => $lastPage]);
		}
	}

	/**
	 * 导出领取记录
	 *
	 * @return mixed
	 */
	public function getCouponsExportData()
	{
		$page  = request('page') ? request('page') : 1;
		$limit = request('limit') ? request('limit') : 20;
		$type  = request('type');

		$condition = $this->getCouponCondition();
		$where     = $condition[1];
		$time      = $condition[0];

		$coupons  = $this->couponRepository->getCouponsPaginated($where, $limit, $time);
		$lastPage = $coupons->lastPage();
		$coupons  = $this->discountService->couponsGetDataExcel($coupons);

		if ($page == 1) {
			session(['export_coupon_get_cache' => generate_export_cache_name('export_coupon_get_cache_')]);
		}
		$cacheName = session('export_coupon_get_cache');

		if ($this->cache->has($cacheName)) {
			$cacheData = $this->cache->get($cacheName);
			$this->cache->put($cacheName, array_merge($cacheData, $coupons), 300);
		} else {
			$this->cache->put($cacheName, $coupons, 300);
		}

		if ($page == $lastPage) {
			$title = ['领取时间', '优惠码', '用户', '是否使用', '使用时间'];

			return $this->ajaxJson(true, ['status' => 'done', 'url' => '', 'type' => $type, 'title' => $title, 'cache' => $cacheName, 'prefix' => 'coupon_get_data_']);
		} else {
			$url_bit = route('admin.shitang.coupon.getCouponsExportData', array_merge(['page' => $page + 1, 'limit' => $limit], request()->except('page', 'limit')));

			return $this->ajaxJson(true, ['status' => 'goon', 'url' => $url_bit, 'page' => $page, 'totalPage' => $lastPage]);
		}
	}
}