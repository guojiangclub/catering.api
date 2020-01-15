<?php

namespace GuoJiangClub\Catering\Backend\Http\Controllers;

use Carbon\Carbon;
use GuoJiangClub\Catering\Backend\Repositories\CouponCenterItemRepository;
use GuoJiangClub\Catering\Backend\Repositories\CouponCenterRepository;
use GuoJiangClub\Catering\Backend\Repositories\DiscountRepository;
use iBrand\Backend\Http\Controllers\Controller;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use Validator;
use DB;

class CouponCenterController extends Controller
{
	protected $discountRepository;
	protected $couponCenterItemRepository;
	protected $couponCenterRepository;

	public function __construct(DiscountRepository $discountRepository, CouponCenterRepository $couponCenterRepository, CouponCenterItemRepository $couponCenterItemRepository)
	{
		$this->discountRepository         = $discountRepository;
		$this->couponCenterItemRepository = $couponCenterItemRepository;
		$this->couponCenterRepository     = $couponCenterRepository;
	}

	public function index()
	{
		$condition  = $this->getCondition();
		$where      = $condition[0];
		$orWhere    = $condition[1];
		$activities = $this->couponCenterRepository->getActivityPaginate($where, $orWhere, ['items.discount'], 15);

		return Admin::content(function (Content $content) use ($activities) {
			$content->description('领劵中心');

			$content->breadcrumb(
				['text' => '活动列表', 'no-pjax' => 1, 'left-menu-active' => '领劵中心']
			);

			$content->body(view('backend-shitang::center.index', compact('activities')));
		});
	}

	private function getCondition()
	{
		$where   = [];
		$orWhere = [];
		$status  = request('status');
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

			$orWhere['status'] = 0;
		}

		if (request('title') != '') {
			$where['title'] = ['like', '%' . request('title') . '%'];
		}

		return [$where, $orWhere];
	}

	public function create()
	{
		return Admin::content(function (Content $content) {
			$content->description('添加活动');

			$content->breadcrumb(
				['text' => '添加活动', 'no-pjax' => 1, 'left-menu-active' => '领劵中心']
			);

			$content->body(view('backend-shitang::center.create'));
		});
	}

	public function edit($id)
	{
		$activity = $this->couponCenterRepository->find($id);

		return Admin::content(function (Content $content) use ($activity) {
			$content->description('修改活动');

			$content->breadcrumb(
				['text' => '修改活动', 'no-pjax' => 1, 'left-menu-active' => '领劵中心']
			);

			$content->body(view('backend-shitang::center.edit', compact('activity')));
		});
	}

	public function store(Request $request)
	{
		$input      = $request->except('_token', 'file');
		$rules      = [
			'title'                              => 'required',
			'activity_banner'                    => 'required',
			'starts_at'                          => 'required|date',
			'ends_at'                            => 'required|date|after:starts_at',
			'discount_coupon_rules'              => 'required|array',
			'discount_coupon_rules.*.couponName' => 'required',
			'discount_coupon_rules.*.couponCode' => 'required',
		];
		$messages   = [
			'required'                       => ':attribute 不能为空',
			'discount_coupon_rules.required' => '请添加优惠券',
			"ends_at.after"                  => ':attribute 不能早于领取开始时间',
		];
		$attributes = [
			'title'                              => '活动名称',
			'activity_banner'                    => '活动banner',
			'discount_coupon_rules.*.couponName' => '优惠券',
			'discount_coupon_rules.*.couponCode' => '优惠券',
			"starts_at"                          => '开始时间',
			"ends_at"                            => '截止时间',
		];
		$validator  = Validator::make($input, $rules, $messages, $attributes);
		if ($validator->fails()) {
			return $this->ajaxJson(false, [], 500, $validator->messages()->first());
		}

		try {

			DB::beginTransaction();

			$data = [
				'title'           => $input['title'],
				'activity_banner' => $input['activity_banner'],
				'starts_at'       => $input['starts_at'],
				'ends_at'         => $input['ends_at'],
				'status'          => $input['status'],
			];
			if (isset($input['id']) && $input['id']) {
				$activity = $this->couponCenterRepository->update($data, $input['id']);
				$activity->items()->delete();
			} else {

				$activity = $this->couponCenterRepository->create($data);
			}

			foreach ($input['discount_coupon_rules'] as $coupon) {
				$this->couponCenterItemRepository->create([
					'coupon_center_id' => $activity->id,
					'discount_id'      => $coupon['couponId'],
					'code'             => $coupon['couponCode'],
				]);
			}

			DB::commit();

			return $this->ajaxJson();
		} catch (\Exception $exception) {
			DB::rollBack();

			\Log::info($exception->getMessage());
			\Log::info($exception->getTraceAsString());

			return $this->ajaxJson(false, [], 500, '保存失败');
		}
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

		return view('backend-shitang::center.coupons', compact('id', 'coupons'));
	}
}