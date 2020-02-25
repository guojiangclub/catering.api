<?php

namespace GuoJiangClub\Catering\Core\Notifications;

use GuoJiangClub\EC\Catering\Notifications\Channels\Wechat;
use GuoJiangClub\EC\Catering\Notifications\Notification;
use Illuminate\Bus\Queueable;

class JoinSuccess extends Notification
{
	use Queueable;

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
		if ($this->checkOpenId($notifiable)) {
			return $this->getData($notifiable);
		}

		return false;
	}

	private function getData($user)
	{

		$template_settings = app('system_setting')->getSetting('wechat_message_st_join_success');
		if (empty($template_settings) || !isset($template_settings['status']) || $template_settings['status'] != 1) {
			return false;
		}

		$user_name = $user->nick_name;
		if (!$user_name) {
			$user_name = $user->mobile;
		}

		$template = [
			'first'    => $template_settings['first'],
			'keyword1' => $user_name,
			'keyword2' => date('Y年m月d日 H:i', strtotime($user->created_at)),
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
			"pagepath" => 'pages/index/index/index',
		];

		return $data;
	}
}
