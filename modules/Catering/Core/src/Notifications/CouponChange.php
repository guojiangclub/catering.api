<?php

namespace GuoJiangClub\Catering\Core\Notifications;

use ElementVip\Component\Point\Model\Point;
use ElementVip\Notifications\Channels\Wechat;
use ElementVip\Notifications\Notification;
use Illuminate\Bus\Queueable;
use ElementVip\Component\Balance\Model\Balance;

class CouponChange extends Notification
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
		if (empty($this->coupon)) {
			return false;
		}

		if ($this->checkOpenId($notifiable)) {
			return $this->getData($notifiable);
		}

		return false;
	}

	private function getData($user)
	{
		$template_settings = app('system_setting')->getSetting('wechat_message_st_coupon_changed');
		if (empty($template_settings) || !isset($template_settings['status']) || $template_settings['status'] != 1) {
			return false;
		}

		$template = [
			'first'    => $template_settings['first'],
			'keyword1' => app('system_setting')->getSetting('shop_name'),  //商户名称
			'keyword2' => $this->coupon['discount'],  //卡券名称
			'keyword3' => $this->coupon['coupon']->created_at . ' 至 ' . $this->coupon['coupon']->expires_at,  //有效期
			'keyword4' => $this->coupon['coupon']->created_at->format('Y-m-d H:i:s'),//办理时间
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
