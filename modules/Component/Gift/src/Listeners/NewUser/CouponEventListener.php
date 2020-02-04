<?php
namespace GuoJiangClub\Catering\Component\Gift\Listeners\NewUser;


use GuoJiangClub\Catering\Component\User\Models\User;
use GuoJiangClub\Catering\Component\Gift\Repositories\GiftActivityRepository;
use GuoJiangClub\Catering\Component\Gift\Models\GiftCouponReceive;
use DB;
use GuoJiangClub\Catering\Component\Discount\Services\DiscountService;

class CouponEventListener
{

    private $giftActivityRepository;
    protected $discountService;

    public function __construct(GiftActivityRepository $giftActivityRepository,DiscountService $discountService)
    {

        $this->giftActivityRepository=$giftActivityRepository;
        $this->discountService=$discountService;
    }

    public function handle(User $user,$activity)
    {
        if(count($activity)>0){
            if($activity->open_coupon&&$activity->is_new_user&&!$activity->is_receive){
                $this->AutoGiveCoupon($user,$activity);
            }
        }
    }


    public function AutoGiveCoupon($user,$activity){
        try {
            if(count($activity->gift)>0){
                foreach ($activity->gift as $item){
                    $num=GiftCouponReceive::where('user_id',$user->id)->where('type','gift_new_user')->where('discount_id',$item->coupon->id)->where('gift_coupon_id',$item->id)->where('type_id',$activity->id)->get();
                    if($item->num>count($num)){
                        DB::beginTransaction();
                        if($couponConvert = $this->discountService->getCouponConvert($item->coupon->code,$user->id)){
                            GiftCouponReceive::create(['type_id'=>$activity->id,'discount_id'=>$item->coupon->id,'user_id'=>$user->id,'type'=>'gift_new_user','gift_coupon_id'=>$item->id]);
                        }
                        DB::commit();
                    }
                }
            }
        } catch (\Exception $exception) {
            \Log::info($exception->getMessage());
        }

    }



}