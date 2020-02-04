<?php

namespace GuoJiangClub\Catering\Backend\Http\Controllers;

use iBrand\Backend\Http\Controllers\Controller;

use GuoJiangClub\Catering\Backend\Repositories\DiscountRepository;
use GuoJiangClub\Catering\Component\Gift\Repositories\GiftCouponRepository;
use GuoJiangClub\Catering\Component\Gift\Services\DirectionalCouponService;
use GuoJiangClub\Catering\Component\User\Models\Group;
use Carbon\Carbon;
use Encore\Admin\Facades\Admin as LaravelAdmin;
use Encore\Admin\Layout\Content;
use GuoJiangClub\Catering\Backend\Repositories\GiftCouponReceiveRepository;
use GuoJiangClub\Catering\Backend\Repositories\GiftDirectionalCouponRepository;
use GuoJiangClub\Catering\Core\Models\GiftDirectionalCoupon;

class DirectionalCouponController extends Controller
{
	protected $discountRepository;

	protected $giftCouponRepository;

	protected $giftDirectionalCouponRepository;

	protected $directionalCouponService;

	protected $giftCouponReceiveRepository;

	public function __construct(
		DiscountRepository $discountRepository,
		GiftCouponRepository $giftCouponRepository,
		GiftDirectionalCouponRepository $giftDirectionalCouponRepository,
		DirectionalCouponService $directionalCouponService,
		GiftCouponReceiveRepository $giftCouponReceiveRepository

	)
	{

		$this->discountRepository              = $discountRepository;
		$this->giftCouponRepository            = $giftCouponRepository;
		$this->giftDirectionalCouponRepository = $giftDirectionalCouponRepository;
		$this->directionalCouponService        = $directionalCouponService;
		$this->giftCouponReceiveRepository     = $giftCouponReceiveRepository;
	}

	public function index()
	{
		$condition = $this->getCondition();

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

		if (request('status') == null) {
			$lists = $this->giftDirectionalCouponRepository->getAll($name);
		} else {
			$lists = $this->giftDirectionalCouponRepository->getAll($name, 0);
		}

		return LaravelAdmin::content(function (Content $content) use ($lists, $coupons) {

			$content->header('定向发券');

			$content->breadcrumb(
				['text' => '定向发券', 'url' => 'shitang/directional/coupon', 'no-pjax' => 1],
				['text' => '活动列表', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '定向发券']

			);

			$content->body(view('backend-shitang::directional_coupon.index', compact('lists', 'coupons')));
		});
	}

	public function searchUser()
	{
		$mobile = [];
		$num    = 0;
		$input  = request()->except(['_token']);

		if ($input['directional_type'] == 'mobile') {
			$mobile_input = explode('#', trim($input['mobile']));

			foreach ($mobile_input as $k => $item) {
				if ($k < 200 And !empty($item)) {
					$mobile[$k] = trim($item);
				}
			}

			if (count($mobile) > 0) {
				$num = $this->directionalCouponService->searchUserByMobileCount($mobile);
			}
		}

		if ($input['directional_type'] == 'custom') {
			$num = $this->directionalCouponService->searchUserByCustom($input, true);
		}

		return $this->ajaxJson(true, ['num' => $num, 'input' => $input], 200, '');
	}

	public function create()
	{
		$groups = [];
		if ($group = Group::all()) {
			$groups = $group;
		}

		return LaravelAdmin::content(function (Content $content) use ($groups) {

			$content->header('定向发券');

			$content->breadcrumb(
				['text' => '定向发券', 'url' => 'shitang/directional/coupon', 'no-pjax' => 1],
				['text' => '创建定向发券', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '定向发券']

			);

			$content->body(view('backend-shitang::directional_coupon.create', compact('groups')));
		});
	}

	public function edit($id)
	{
		$gift   = $this->giftDirectionalCouponRepository->with('coupon')->find($id);
		$groups = [];
		if ($group = Group::all()) {
			$groups = $group;
		}

		return LaravelAdmin::content(function (Content $content) use ($groups, $gift) {

			$content->header('编辑定向发券');

			$content->breadcrumb(
				['text' => '定向发券', 'url' => 'shitang/directional/coupon', 'no-pjax' => 1],
				['text' => '编辑定向发券', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '定向发券']

			);

			$content->body(view('backend-shitang::directional_coupon.edit', compact('groups', 'gift')));
		});
	}

	public function store()
	{
		$input = request()->except(['_token']);
		unset($input['coupon_title']);
		$input['status']   = 1;
		$input['group_id'] = isset($input['group_id']) ? intval($input['group_id']) : 0;
		if (empty($input['buy_price_above'])) {
			unset($input['buy_price_above']);
		}
		if (empty($input['buy_price_below'])) {
			unset($input['buy_price_below']);
		}

		if ($res = $this->giftDirectionalCouponRepository->create($input)) {
			event('st.directional.coupon', [$res->id]);

			return $this->ajaxJson(true, [], 200, '');
		}

		return $this->ajaxJson(false, [], 400, '');
	}

	public function coupon_api()
	{
		$condition = $this->getCondition();
		$where     = $condition[0];
		$orWhere   = $condition[1];
		$coupons   = $this->discountRepository->getDiscountList($where, $orWhere);

		return $this->ajaxJson(true, $coupons, 200, '');
	}

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

		$where['type'] = ['<>', 1];

		return [$where, $orWhere];
	}

	public function destroy()
	{
		$id = request('id');
		if ($user = GiftDirectionalCoupon::find($id)) {
			$user->status = 0;
			$user->save();

			return $this->ajaxJson(true, [], 200, '');
		}

		return $this->ajaxJson(false, [], 400, '操作失败');
	}

	public function log($id)
	{

		$mobile = request('mobile');

		$coupons = $this->giftCouponReceiveRepository->getCouponsRecord($id, 'gift_directional_coupon', $mobile);

		return LaravelAdmin::content(function (Content $content) use ($coupons) {

			$content->header('定向发券明细表');

			$content->breadcrumb(
				['text' => '定向发券', 'url' => 'shitang/directional/coupon', 'no-pjax' => 1],
				['text' => '定向发券明细表', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '定向发券']

			);

			$content->body(view('backend-shitang::directional_coupon.show', compact('coupons')));
		});
	}
}