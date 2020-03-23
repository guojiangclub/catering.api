<?php

namespace GuoJiangClub\Catering\Component\Recharge\Listeners;

use GuoJiangClub\Catering\Component\Balance\Model\BalanceOrder;
use GuoJiangClub\Catering\Component\Recharge\Repositories\RechargeRuleRepository;
use GuoJiangClub\Catering\Component\Recharge\Models\GiftCouponReceive;
use GuoJiangClub\Catering\Component\Discount\Services\DiscountService;
use DB;

class CouponEventListener
{
    private   $rechargeRuleRepository;
    protected $discountService;

    public function __construct(RechargeRuleRepository $rechargeRuleRepository, DiscountService $discountService)
    {

        $this->rechargeRuleRepository = $rechargeRuleRepository;
        $this->discountService        = $discountService;
    }

    public function handle(BalanceOrder $order)
    {
        if ($recharge = $this->rechargeRuleRepository->getRechargeByID($order->recharge_rule_id)) {
            if ($recharge->open_coupon && $order->pay_status) {
                $this->AutoGiveCoupon($order, $recharge);
            }
        }
    }

    public function AutoGiveCoupon($order, $recharge)
    {
        try {
            if ($recharge->open_coupon && count($recharge->gift) > 0) {
                foreach ($recharge->gift as $item) {
                    $num = GiftCouponReceive::where('user_id', $order->user_id)->where('origin_id', $order->id)->where('origin_type', BalanceOrder::class)->where('discount_id', $item->coupon->id)->where('gift_coupon_id', $item->id)->get();
                    if ($item->num > count($num)) {

                        if ($couponConvert = $this->discountService->getCouponConvert($item->coupon->code, $order->user_id)) {
                            GiftCouponReceive::create(['origin_id' => $order->id, 'origin_type' => BalanceOrder::class, 'discount_id' => $item->coupon->id, 'user_id' => $order->user_id, 'type' => 'gift_recharge', 'gift_coupon_id' => $item->id]);
                        }
                    }
                }
            }
        } catch (\Exception $exception) {
            \Log::info($exception->getMessage());
        }
    }

}