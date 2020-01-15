<?php

namespace GuoJiangClub\Catering\Backend\Http\Controllers;

use iBrand\Backend\Http\Controllers\Controller;
use GuoJiangClub\Catering\Backend\Repositories\DiscountRepository;
use DB;
use GuoJiangClub\Catering\Backend\Repositories\GiftActivityRepository;
use Carbon\Carbon;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use GuoJiangClub\Catering\Backend\Models\GiftDiscount;
use Illuminate\Http\Request;
use Validator;

class GiftBirthdayController extends Controller
{
    protected $discountRepository;
    protected $giftActivityRepository;

    public function __construct(
        DiscountRepository $discountRepository,
        GiftActivityRepository $giftActivityRepository)
    {
        $this->discountRepository = $discountRepository;
        $this->giftActivityRepository = $giftActivityRepository;
    }

    public function index()
    {
        $coupons = $this->discountRepository->findActive(1);
        if (count($coupons) > 0) {
            $coupons = $coupons->pluck('id')->all();
        }

        $lists = $this->giftActivityRepository->getGiftActivityPaginated('gift_birthday');

        return Admin::content(function (Content $content) use ($lists, $coupons) {

            $content->header('会员生日礼');

            $content->breadcrumb(
                ['text' => '会员生日礼', 'no-pjax' => 1, 'left-menu-active' => '生日礼']
            );

            $content->body(view('backend-shitang::birthday.index', compact('lists', 'coupons')));
        });
    }

    public function create()
    {
        $coupons = $this->discountRepository->findActive(1);

        return Admin::content(function (Content $content) use ($coupons) {

            $content->header('添加活动');

            $content->breadcrumb(
                ['text' => '添加活动', 'no-pjax' => 1, 'left-menu-active' => '生日礼']
            );

            $content->body(view('backend-shitang::birthday.create', compact('coupons')));
        });
    }

    public function edit($id)
    {
        $activity = $this->giftActivityRepository->find($id);
        $selected = $activity->gift()->pluck('discount_id')->all();
        $coupons = $this->discountRepository->findActive(1);

        return Admin::content(function (Content $content) use ($activity, $coupons, $selected) {

            $content->header('编辑活动');

            $content->breadcrumb(
                ['text' => '编辑活动', 'no-pjax' => 1, 'left-menu-active' => '生日礼']
            );

            $content->body(view('backend-shitang::birthday.edit', compact('activity', 'coupons', 'selected')));
        });
    }

    public function store(Request $request)
    {
        $input = $request->except(['_token']);
        $rules = [
            'name' => 'required',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            //   'coupons' => 'required|array',
        ];

        $message = [
            "required" => ":attribute 不能为空",
            "ends_at.after" => ':attribute 不能早于 活动有效开始时间',
            //    "coupons.array" => ':attribute 不能为空',
        ];

        $attributes = [
            'name' => '生日礼名称',
            'starts_at' => '活动有效开始时间',
            'ends_at' => '活动有效截止时间',
            'coupons' => '赠送优惠券',
        ];

        $validator = Validator::make($input, $rules, $message, $attributes);
        if ($validator->fails()) {
            $warnings = $validator->messages();
            $show_warning = $warnings->first();

            return $this->ajaxJson(false, [], 404, $show_warning);
        }

        if ($input['status']) {
            $where = ['status' => 1, 'activity_type' => 'gift_birthday', ['starts_at', '<=', Carbon::now()], ['ends_at', '>', Carbon::now()]];
            if (isset($input['id']) && $input['id']) {
                array_push($where, ['id', '!=', $input['id']]);
            }

            $activity = $this->giftActivityRepository->findWhere($where)->first();
            if ($activity) {
                return $this->ajaxJson(false, [], 500, '当前已有正在进行的生日礼活动');
            }
        }

        $input['activity_type'] = 'gift_birthday';
        $coupons = $input['coupons'];
        unset($input['coupons']);

        try {
            DB::beginTransaction();

            if (isset($input['id']) && $input['id']) {
                $activity = $this->giftActivityRepository->update($input, $input['id']);
                $activity->gift()->delete();
            } else {
                $activity = $this->giftActivityRepository->create($input);
            }

            foreach ($coupons as $coupon_id) {
                $data['gift_activity_id'] = $activity->id;
                $data['discount_id'] = $coupon_id;
                GiftDiscount::create($data);
            }

            DB::commit();

            return $this->ajaxJson();
        } catch (\Exception $exception) {
            DB::rollBack();
            \Log::info($exception->getTraceAsString());
            \Log::info($exception->getMessage());
            return $this->ajaxJson(false, [], 400, $exception->getMessage());
        }
    }

    public function delete($id)
    {
        $activity = $this->giftActivityRepository->find($id);

        $activity->gift()->delete();
        $activity->delete();

        return $this->ajaxJson();
    }

    public function toggleStatus(Request $request)
    {
        $status = $request->input('status');
        $status = $status == 1 ? 0 : 1;
        $id = $request->input('id');
        $activity = $this->giftActivityRepository->find($id);
        if ($status && !$activity->activity_date_status) {
            return $this->ajaxJson(false, [], 400, '启动失败，活动未开始或已结束');
        }

        $where = ['status' => 1, 'activity_type' => 'gift_birthday', ['starts_at', '<=', Carbon::now()], ['ends_at', '>', Carbon::now()], ['id', '!=', $id]];
        $has_activity = $this->giftActivityRepository->findWhere($where)->first();
        if ($status && $has_activity) {
            return $this->ajaxJson(false, [], 400, '启动失败，当前集市已有正在进行的活动');
        }

        if ($activity) {
            $activity->status = $status;
            $activity->save();

            return $this->ajaxJson();
        }

        return $this->ajaxJson(false, [], 400, '操作失败');
    }
}