<?php

namespace GuoJiangClub\Catering\Server\Http\Controllers;

use Carbon\Carbon;
use GuoJiangClub\Catering\Backend\Models\GiftActivity;
use GuoJiangClub\Catering\Backend\Repositories\GiftActivityRepository;
use GuoJiangClub\Catering\Server\Repositories\CouponRepository;
use GuoJiangClub\Catering\Server\Repositories\DiscountRepository;

class GiftNewUserController extends Controller
{
    protected $giftActivityRepository;
    protected $discountRepository;

    public function __construct(GiftActivityRepository $giftActivityRepository,
                                DiscountRepository $discountRepository,
                                CouponRepository $couponRepository)
    {
        $this->giftActivityRepository = $giftActivityRepository;
        $this->discountRepository = $discountRepository;
        $this->couponRepository = $couponRepository;
    }

    public function index()
    {
        $where = ['status' => 1, 'activity_type'=>'gift_new_user', ['starts_at', '<=', Carbon::now()], ['ends_at', '>', Carbon::now()]];
        $activity = $this->giftActivityRepository->findWhere($where)->first();
        if (!$activity) {
            return $this->success(['dialog' => false]);
        }

        $gift_discounts = $activity->gift()->pluck('discount_id')->all();
        if (empty($gift_discounts)) {
            return $this->success(['dialog' => false]);
        }

        $discounts = $this->discountRepository->findActive(1);
        $discount_ids = $discounts->pluck('id')->all();
        $filter = collect($gift_discounts)->filter(function ($item) use ($discount_ids) {
            return in_array($item, $discount_ids);
        });

        if (count($filter) <= 0) {
            return $this->success(['dialog' => false]);
        }

        if (count($filter) > 1 && $activity->type == GiftActivity::TYPE_RANDOM) {
            $filter = collect([$filter->random()]);
        }

        $data = [];
        foreach ($filter as $item) {
            $discount = $discounts->where('id', $item)->first();
            $data[] = $discount->toArray();
        }

        if (!empty($data)) {
            return $this->success(['coupons' => $data, 'dialog' => true]);
        } else {
            return $this->success(['dialog' => false]);
        }
    }
}