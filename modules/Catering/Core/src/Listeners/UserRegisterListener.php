<?php

namespace GuoJiangClub\Catering\Core\Listeners;

use Carbon\Carbon;
use GuoJiangClub\Catering\Core\Auth\User;
use GuoJiangClub\Catering\Component\User\Models\UserBind;
use GuoJiangClub\Catering\Backend\Models\GiftActivity;
use GuoJiangClub\Catering\Core\Models\GiftCouponReceive;
use GuoJiangClub\Catering\Backend\Repositories\GiftActivityRepository;
use GuoJiangClub\Catering\Server\Repositories\CouponRepository;
use GuoJiangClub\Catering\Server\Repositories\DiscountRepository;
use GuoJiangClub\Catering\Component\Point\Repository\PointRepository;
use GuoJiangClub\Catering\Server\Service\DiscountService;
use QrCode;

class UserRegisterListener
{
	protected $giftActivityRepository;
	protected $discountRepository;
	protected $point;
	protected $discountService;

	public function __construct(GiftActivityRepository $giftActivityRepository,
	                            DiscountRepository $discountRepository,
	                            CouponRepository $couponRepository,
	                            PointRepository $pointRepository,
	                            DiscountService $discountService
	)
	{
		$this->giftActivityRepository = $giftActivityRepository;
		$this->discountRepository     = $discountRepository;
		$this->couponRepository       = $couponRepository;
		$this->point                  = $pointRepository;
		$this->discountService        = $discountService;
	}

	public function newUserGift($user)
	{
		$where    = ['status' => 1, 'activity_type' => 'gift_new_user', ['starts_at', '<=', Carbon::now()], ['ends_at', '>', Carbon::now()]];
		$activity = $this->giftActivityRepository->findWhere($where)->first();
		if (!$activity) {
			return;
		}

		try {
			//赠送积分
			if ($activity->point AND $this->point->getRecordByAction($user->id, 'new_user_gift')) {
				$this->point->create(['user_id' => $user->id, 'action' =>
					'new_user_gift', 'note'     => '新人礼', 'item_type' => User::class,
				                      'item_id' => $user->id
				                      , 'value' => $activity->point]);

				//TODO::发送模板消息,暂时用这个事件，事件未写
				event('point.change', $user->id);
				event('st.wechat.message.point', [$user, '注册赠送积分', $activity->point]);
			}

			//赠送优惠券
			if (!$activity->gift->count()) { //活动没有关联优惠券
				return;
			}

			$discount_ids = $activity->gift->pluck('discount_id')->toArray();

			$discounts = $this->discountRepository->getDiscountByIds($discount_ids, 1);
			if (!$discounts->count()) {  //活动关联的优惠券无效
				return;
			}

			if (GiftCouponReceive::where('user_id', $user->id)
				->where('origin_type', 'gift_new_user')
				->where('origin_id', $activity->id)
				->first()) {  //已领取过
				return;
			}

			if ($activity->type == GiftActivity::TYPE_RANDOM) {
				$discounts = $discounts->random(1);
			}

			foreach ($discounts as $discount) {
				$couponConvert = $this->discountService->getCouponConvert($discount->code, $user->id);
				if ($couponConvert) {
					GiftCouponReceive::create([
						'origin_type' => 'gift_new_user',
						'origin_id'   => $activity->id,
						'user_id'     => $user->id,
						'discount_id' => $couponConvert->discount_id,
						'coupon_id'   => $couponConvert->id,
					]);

					$qrCodeSavePath = 'user/coupon/' . $user->id . '_' . $couponConvert->code . '.png';
					if (!\Storage::disk('public')->exists($qrCodeSavePath)) {
						$res = QrCode::format('png')->size(200)->margin(1)->errorCorrection('H')->generate($couponConvert->code);
						\Storage::disk('public')->put($qrCodeSavePath, $res);
					}

					$couponConvert->coupon_use_code = \Storage::disk('public')->url($qrCodeSavePath);
					$couponConvert->save();

					event('st.wechat.message.coupon', [$user, $couponConvert]);
				}
			}
		} catch (\Exception $exception) {
			\Log::info($exception->getMessage());
		}
	}

	public function newUserFromAgent($user, $agent_code)
	{
		try {
			if ($shareCouponID = settings('sharer_get_coupon')) {
				$shareUser = User::where('confirmation_code', $agent_code)->first();
				$discount  = $this->discountRepository->find($shareCouponID);

				$couponConvert = $this->discountService->getCouponConvert($discount->code, $shareUser->id);
				if ($couponConvert) {
					$qrCodeSavePath = 'user/coupon/' . $user->id . '_' . $couponConvert->code . '.png';
					if (!\Storage::disk('public')->exists($qrCodeSavePath)) {
						$res = QrCode::format('png')->size(200)->margin(1)->errorCorrection('H')->generate($couponConvert->code);
						\Storage::disk('public')->put($qrCodeSavePath, $res);
					}

					$couponConvert->coupon_use_code = \Storage::disk('public')->url($qrCodeSavePath);
					$couponConvert->save();
				}
			}

			if ($couponID = settings('sharee_get_coupon')) {
				$discount = $this->discountRepository->find($couponID);

				$couponConvert = $this->discountService->getCouponConvert($discount->code, $user->id);
				if ($couponConvert) {
					$qrCodeSavePath = 'user/coupon/' . $user->id . '_' . $couponConvert->code . '.png';
					if (!\Storage::disk('public')->exists($qrCodeSavePath)) {
						$res = QrCode::format('png')->size(200)->margin(1)->errorCorrection('H')->generate($couponConvert->code);
						\Storage::disk('public')->put($qrCodeSavePath, $res);
					}

					$couponConvert->coupon_use_code = \Storage::disk('public')->url($qrCodeSavePath);
					$couponConvert->save();
				}
			}
		} catch (\Exception $exception) {
			\Log::info($exception->getTraceAsString());
			\Log::info($exception->getMessage());
		}
	}

	public function userScanWechat($wechatInfo)
	{
		$wxInfo = wechat_channel()->userInfo($wechatInfo['openid']);
		\Log::info([$wxInfo]);
		if (!isset($wxInfo->errcode) && $wxInfo) {
			if (isset($wxInfo->unionid)) {
				$weChatUserBind = UserBind::where('unionid', $wxInfo->unionid)->where('type', 'wechat')->first();
				if (!$weChatUserBind) {
					$weChatUserBind = UserBind::create([
						'type'    => 'wechat',
						'open_id' => $wxInfo->openid,
						'unionid' => $wxInfo->unionid,
						'app_id'  => settings('wechat_app_id'),
					]);
				}

				$miniProgramUserBind = UserBind::where('unionid', $wxInfo->unionid)->where('type', 'miniprogram')->first();
				if ($miniProgramUserBind) {
					$weChatUserBind->user_id = $miniProgramUserBind->user_id;
					$weChatUserBind->save();
				}
			}
		}
	}

	public function subscribe($events)
	{
		$events->listen(
			'user.register.newUserGift',
			'GuoJiangClub\Catering\Core\Listeners\UserRegisterListener@newUserGift'
		);

		$events->listen(
			'user.register.agent.share',
			'GuoJiangClub\Catering\Core\Listeners\UserRegisterListener@newUserFromAgent'
		);

		$events->listen(
			'user.subscribe.official_account',
			'GuoJiangClub\Catering\Core\Listeners\UserRegisterListener@userScanWechat'
		);
	}
}