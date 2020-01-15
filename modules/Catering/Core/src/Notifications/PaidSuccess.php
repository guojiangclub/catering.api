<?php

namespace GuoJiangClub\Catering\Core\Notifications;

use ElementVip\Notifications\Channels\Wechat;
use ElementVip\Notifications\Notification;
use Illuminate\Bus\Queueable;

class PaidSuccess extends Notification
{
	use Queueable;

	protected $order;

	protected $remark;

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
		$this->order = $this->data['order'];

		$this->remark = $this->data['remark'];

		if ($this->checkOpenId($notifiable)) {
			return $this->getData($notifiable);
		}

		return false;
	}

	private function getData($user)
	{

		$template_settings = app('system_setting')->getSetting('wechat_message_st_paid_success');
		if (empty($template_settings) || !isset($template_settings['status']) || $template_settings['status'] != 1) {
			return false;
		}

		$template = [
			'first'    => $template_settings['first'],
			'keyword1' => $this->order->order_no,
			'keyword2' => $this->order->total_yuan . '元',
			'keyword3' => app('system_setting')->getSetting('shop_name'),
			'keyword4' => date('Y年m月d日 H:i:s', strtotime($this->order->created_at)),
			'remark'   => $this->remark,
		];

		$data = [
			'template_id' => $template_settings['template_id'],
			'url'         => '',
			'touser'      => $this->getOpenId($user),
			'data'        => $template,
		];

		$data["miniprogram"] = [
			"appid"    => env('SHITANG_MINI_PROGRAM_APPID'),
			"pagepath" => 'pages/order/index/index',
		];

		return $data;
	}
}
