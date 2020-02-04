<?php

namespace GuoJiangClub\Catering\Component\Gift\Listeners\Birthday;

use GuoJiangClub\Catering\Component\Point\Repository\PointRepository;
use GuoJiangClub\Catering\Component\User\Models\User;
use GuoJiangClub\Catering\Component\Gift\Repositories\GiftActivityRepository;
use GuoJiangClub\Catering\Component\User\Models\UserBind;
use GuoJiangClub\Catering\Component\Gift\Models\GiftActivity;
use Carbon\Carbon;

class PointEventListener
{
	private $pointRepository;
	private $giftActivityRepository;

	public function __construct(PointRepository $pointRepository, GiftActivityRepository $giftActivityRepository

	)
	{
		$this->pointRepository        = $pointRepository;
		$this->giftActivityRepository = $giftActivityRepository;
	}

	public function handle(User $user, $activity)
	{

		if (count($activity) > 0) {
			if (settings('point_enabled') && $activity->point && !$activity->is_receive) {
				$this->AutoGivePoint($user, $activity);
			}
		}
	}

	public function AutoGivePoint($user, $activity)
	{
		try {
			$point_status = false;
			$time         = Carbon::now()->timestamp;
			$birthday     = date('Y-m-d', $time);
			$point        = $this->pointRepository->orderBy('created_at', 'desc')->findWhere(['action' => 'gift_birthday_point', 'user_id' => $user->id])->first();
			if ($point) {
				if (intval(strtotime(date('Y-m-d', strtotime($point->created_at))) !== intval(strtotime($birthday)))) {
					$point_status = true;
				}
			}
			if ($point == null || $point_status) {
				$this->pointRepository->create(['user_id' => $user->id, 'action' =>
					'gift_birthday_point', 'note'         => date('Y', $time) . '年生日礼赠送积分', 'item_type' => GiftActivity::class,
				                                'item_id' => $activity->id
				                                , 'value' => $activity->point]);
				if ($this->pointRepository->updateUserPoint($user->id)) {
					$this->WeChatTemplateMessage($user, $activity);
				}
			}
		} catch (\Exception $exception) {
			\Log::info($exception->getMessage());
		}
	}

//会员积分变动提醒
	public function WeChatTemplateMessage($user, $activity)
	{
		try {
			$message = settings('wechat_message_point_changed');
			if (!isset($message['status']) && !isset($message['template_id'])) {
				if (empty($message['status']) || empty($message['template_id'])) {
					return;
				}
			}
			$template_id       = $message['template_id'];
			$mobile_domain_url = settings('mobile_domain_url');
			$app_id            = settings('wechat_app_id');
			$userBind          = UserBind::where('type', 'wechat')->where('user_id', $user->id)->where('app_id', $app_id)->first();
			if (!$userBind) {
				$userBind = UserBind::where('type', 'wechat')->where('user_id', $user->id)->first();
			}
			if ($userBind) {
				if (!empty($user->nick_name)) {
					$name = $user->nick_name;
				} elseif (!empty($user->name)) {
					$name = $user->name;
				} else {
					$name = $user->mobile;
				}
				if (isset($userBind->open_id)) {
					$data = [
						'template_id' => $template_id,
						'url'         => $mobile_domain_url . '/#!/user/point',
						'touser'      =>
							[$userBind->open_id],
						'data'        => [
							'first'    => '生日奖励' . $activity->point . '积分已到账',
							'keyword1' => $name,
							'keyword2' => isset($user->card->number) ? $user->card->number : '',
							'keyword3' => '亲爱的用户，即将生日' . '系统赠送' . $activity->point . '积分',
							'remark'   => $activity->title,
						],
					];
					$http = settings('wechat_api_url');
					$url  = $http . "api/notice/sendall?appid=" . $app_id;
					app('wechat.channel')->TemplateMessage($url, $data);
				}
			}
		} catch (\Exception $exception) {
			\Log::info($exception->getMessage());
		}
	}

}