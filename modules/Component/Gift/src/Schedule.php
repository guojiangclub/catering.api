<?php

namespace GuoJiangClub\Catering\Component\Gift;

use Carbon\Carbon;
use GuoJiangClub\Catering\Component\Scheduling\Schedule\Scheduling;
use GuoJiangClub\Catering\Component\Point\Model\Point;
use GuoJiangClub\Catering\Component\Gift\Models\GiftActivity;
use GuoJiangClub\Catering\Component\User\Models\UserBind;
use GuoJiangClub\Catering\Component\User\Models\User;
use DB;

class Schedule extends Scheduling
{
	public function schedule()
	{

		$this->schedule->call(function () {
			$birthday      = $this->giftBirthday();
			$point_enabled = app('system_setting')->getSetting('point_enabled');

			if ($birthday && $birthday->point && $point_enabled) {
				$time = Carbon::now()->timestamp;
				$date = date('Y-m-d', $time);
				$day  = $birthday->activity_day - 1;
				$one  = "SELECT * FROM el_card WHERE 
          DATEDIFF(CAST(CONCAT(YEAR('$date'),DATE_FORMAT(birthday,'-%m-%d'))AS DATE),CAST(DATE_FORMAT('$date','%y-%m-%d') AS DATE)) BETWEEN 0 AND $day";
				$two  = "DATEDIFF(CAST(CONCAT(YEAR('$date')+1,DATE_FORMAT(birthday,'-%m-%d'))AS DATE),CAST(DATE_FORMAT('$date','%y-%m-%d') AS DATE)) BETWEEN 0 AND $day";
				$user = DB::select($one . ' OR ' . $two);
				if (count($user) > 0) {
					\Log::info('gift_birthday定时任务送积分');
					$this->userGiftBirthdayPoint($user, $birthday);
				}
			}
		})->dailyAt('10:00');
	}

	private function giftBirthday()
	{
		return GiftActivity::where('type', 'gift_birthday')->where('status', 1)
			->where('ends_at', '>=', Carbon::now())
			->where('starts_at', '<=', Carbon::now())
			->first();
	}

	private function userGiftBirthdayPoint($user, $activity)
	{
		try {
			foreach ($user as $item) {
				if ($item->name && $item->mobile && $item->birthday) {
					$point_status = false;
					$time         = Carbon::now()->timestamp;
					$birthday     = date('Y-m-d', $time);
					$point        = Point::where('action', 'gift_birthday_point')
						->where('user_id', $item->user_id)
						->orderBy('created_at', 'desc')
						->first();
					if ($point) {
						if (intval(strtotime(date('Y-m-d', strtotime($point->created_at))) !== intval(strtotime($birthday)))) {
							$point_status = true;
						}
					}

					if ($point == null || $point_status) {
						Point::create(['user_id'          => $item->user_id, 'action' =>
							'gift_birthday_point', 'note' => date('Y', $time) . '年生日礼赠送积分', 'item_type' => GiftActivity::class,
						               'item_id'          => $activity->id
						               , 'value'          => $activity->point]);
						if ($this->updateUserPoint($item->user_id)) {
							$this->WeChatTemplateMessage($item->user_id, $activity);
						}
					}
				}
			}
		} catch (\Exception $exception) {
			\Log::info($exception->getMessage());
		}
	}

	private function WeChatTemplateMessage($user_id, $activity)
	{
		try {
			$user    = User::find($user_id);
			$message = app('system_setting')->getSetting('wechat_message_point_changed');
			if (!isset($message['status']) && !isset($message['template_id'])) {
				if (empty($message['status']) || empty($message['template_id'])) {
					return;
				}
			}
			$template_id       = $message['template_id'];
			$mobile_domain_url = app('system_setting')->getSetting('mobile_domain_url');
			$app_id            = app('system_setting')->getSetting('wechat_app_id');

			$userBind = UserBind::where('type', 'wechat')->where('user_id', $user->id)->where('app_id', $app_id)->first();
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
						'url'         => $mobile_domain_url . '/m/#!/user/point',
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
					$http = app('system_setting')->getSetting('wechat_api_url');
					$url  = $http . "api/notice/sendall?appid=" . $app_id;
					app('wechat.channel')->TemplateMessage($url, $data);
				}
			}
		} catch (\Exception $exception) {
			\Log::info($exception->getMessage());
		}
	}

	private function updateUserPoint($uid)
	{
		$user = config('point.user_model');
		$user = new $user();
		$user = $user::find($uid);
		if ($user) {
			$user->integral           = $this->getSumPoint($uid, 'default');
			$user->available_integral = $this->getSumPointValid($uid, 'default');
			$user->save();

			return true;
		}

		return false;
	}

	public function getSumPoint($id, $type = null)
	{
		if ($type !== null) {
			$sum = Point::where([
				'user_id' => $id,
				'type'    => $type,
			])->sumPoint();
		} else {
			$sum = Point::where('user_id', $id)->sumPoint();
		}

		return $this->getSumNumeric($sum);
	}

	public function getSumPointValid($id, $type = null)
	{
		if ($type !== null) {
			$sum = Point::where([
				'user_id' => $id,
				'type'    => $type,
			])->valid()->sumPoint();
		} else {
			$sum = Point::where('user_id', $id)->valid()->sumPoint();
		}

		return $this->getSumNumeric($sum);
	}

	private function getSumNumeric($sum)
	{
		if (!is_numeric($sum)) {
			return 0;
		}

		return $sum;
	}

}