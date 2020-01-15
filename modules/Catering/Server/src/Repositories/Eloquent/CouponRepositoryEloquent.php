<?php

namespace GuoJiangClub\Catering\Server\Repositories\Eloquent;

use Carbon\Carbon;
use GuoJiangClub\Catering\Component\Discount\Repositories\Eloquent\CouponRepositoryEloquent as BaseCouponRepositoryEloquent;
use GuoJiangClub\Catering\Backend\Models\Coupon\Coupon;
use GuoJiangClub\Catering\Backend\Models\Coupon\Discount;
use GuoJiangClub\Catering\Server\Repositories\CouponRepository;

class CouponRepositoryEloquent extends BaseCouponRepositoryEloquent implements CouponRepository
{
    public function model()
    {
        return Coupon::class;
    }

    public function getCouponsByUserID($user_id, $coupon_id, $utmCampaign = null, $utmSource = null)
    {
        $discount = Discount::find($coupon_id);

        if (!isset($discount->usage_limit) || $discount->usage_limit < 1) {
            return false;
        }

        $input['channel'] = $discount->channel;
        $input['user_id'] = $user_id;
        $input['discount_id'] = $coupon_id;

        if ($discount->effective_days) {
            $input['expires_at'] = Carbon::now()->addDays($discount->effective_days);
        } elseif ($discount->useend_at) {
            $input['expires_at'] = $discount->useend_at;
        } else {
            $input['expires_at'] = $discount->ends_at;
        }

        $input['code'] = build_order_no('ST');

        if ($utmCampaign) {
            $input['utm_campaign'] = $utmCampaign;
        }

        if ($utmSource) {
            $input['utm_source'] = $utmSource;
        }

        $coupon = $this->create($input);

        if ($coupon) {
            $incrementDisount = Discount::where(['id' => $coupon_id])->increment('used');
            if ($incrementDisount) {

                return $coupon;
            }
        }

        return false;
    }
}