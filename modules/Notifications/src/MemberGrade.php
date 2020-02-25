<?php

namespace GuoJiangClub\EC\Catering\Notifications;

use GuoJiangClub\EC\Catering\Notifications\Channels\Wechat;
use Illuminate\Bus\Queueable;

class MemberGrade extends Notification
{
	use Queueable;

	protected $member;

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
		$this->member = $this->data['member'];
		if (empty($this->member)) {
			return false;
		}

		if ($this->checkOpenId($notifiable)) {
			return $this->getData($notifiable);
		}

		return false;
	}

	private function getData($user)
	{
		$template_settings = app('system_setting')->getSetting('wechat_message_member_grade');
		if (empty($template_settings) || !isset($template_settings['status']) || $template_settings['status'] != 1) {
			return false;
		}

		$template = [
			'first'    => $template_settings['first'],
			'keyword1' => $this->member['original_grade'],
			'keyword2' => $this->member['new_grade'],
			'remark'   => $template_settings['remark'],
		];
		$url      = app('system_setting')->getSetting('mobile_domain_url');
		$data     = [
			'template_id' => $template_settings['template_id'],
			'url'         => $url . '/#!/user',
			'touser'      => $this->getOpenId($user),
			'data'        => $template,
		];

        $data["miniprogram"] = [
            "appid" => env('SHITANG_MINI_PROGRAM_APPID'),
            "pagepath" => 'aa'
        ];

		return $data;
	}
}
