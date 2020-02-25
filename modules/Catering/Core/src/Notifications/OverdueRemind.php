<?php

namespace GuoJiangClub\Catering\Core\Notifications;

use GuoJiangClub\EC\Catering\Notifications\Channels\Wechat;
use GuoJiangClub\EC\Catering\Notifications\Notification;
use Illuminate\Bus\Queueable;

class OverdueRemind extends Notification
{
	use Queueable;

	protected $coupon;

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
		$this->coupon = $this->data['coupon'];

		if ($this->checkOpenId($notifiable)) {
			return $this->getData($notifiable);
		}

		return false;
	}

	private function getData($user)
	{

		$template_settings = app('system_setting')->getSetting('wechat_message_st_coupon_overdue_remind');
		if (empty($template_settings) || !isset($template_settings['status']) || $template_settings['status'] != 1) {
			return false;
		}

		$template = [
			'first'    => $template_settings['first'],
			'keyword1' => app('system_setting')->getSetting('shop_name'),
			'keyword2' => date('Y年m月d日 H:i', strtotime($this->coupon->expires_at)),
			'keyword3' => $this->coupon->discount->title,
			'keyword4' => '三天后过期',
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
			"pagepath" => 'pages/coupon/index/index',
		];

		return $data;
	}
}
