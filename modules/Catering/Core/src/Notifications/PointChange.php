<?php

namespace GuoJiangClub\Catering\Core\Notifications;

use ElementVip\Component\Point\Model\Point;
use ElementVip\Notifications\Channels\Wechat;
use ElementVip\Notifications\Notification;
use Illuminate\Bus\Queueable;
use ElementVip\Component\Balance\Model\Balance;

class PointChange extends Notification
{
	use Queueable;

	protected $point;

	/**
	 * Get the notification's delivery channels.
	 *
	 * @param  mixed $notifiable
	 *
	 * @return array
	 */
	public function via($notifiable)
	{
		return [Wechat::class];
	}

	/**
	 * @param $notifiable
	 *
	 * @return array|bool
	 */
	public function handle($notifiable)
	{
		$this->point = $this->data['point'];
		if (empty($this->point)) {
			return false;
		}

		if ($this->checkOpenId($notifiable)) {
			return $this->getData($notifiable);
		}

		return false;
	}

	private function getData($user)
	{
		$template_settings = app('system_setting')->getSetting('wechat_message_st_point_changed');
		if (empty($template_settings) || !isset($template_settings['status']) || $template_settings['status'] != 1) {
			return false;
		}

		$sum      = Point::where('user_id', $user->id)->valid()->sumPoint();
		$template = [
			'first'    => $template_settings['first'],
			'keyword1' => $this->point['card_no'],  //卡号
			'keyword2' => $this->point['note'] . $this->point['value'],  //变更类型
			'keyword3' => $sum,  //当前积分
			'remark'   => $template_settings['remark'],
		];

		$data = [
			'template_id' => $template_settings['template_id'],
			'url'         => '',
			'touser'      => $this->getOpenId($user),
			'data'        => $template,
		];

		$data["miniprogram"] = [
			"appid"    => env('SHITANG_MINI_PROGRAM_APPID'),
			"pagepath" => 'pages/point/index/index',
		];

		return $data;
	}
}
