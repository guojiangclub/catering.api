<?php

namespace GuoJiangClub\EC\Catering\Notifications;

use GuoJiangClub\EC\Catering\Notifications\Channels\Wechat;
use Illuminate\Bus\Queueable;

class GrouponFailed extends Notification
{
    use Queueable;

    protected $grouponUser;
    protected $order;

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
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
        $this->grouponUser = $this->data['grouponUser'];
        $this->order       = $this->data['order'];

        if (empty($this->grouponUser)) {
            return false;
        }

        if ($this->checkOpenId($notifiable)) {
            return $this->getData($notifiable);
        }

        return false;
    }

    private function getData($user)
    {
        $template_settings = app('system_setting')->getSetting('wechat_message_groupon_failed');
        if (empty($template_settings) || !isset($template_settings['status']) || $template_settings['status'] != 1) {
            return false;
        }
        $groupon = $this->grouponUser->belongsToGroupon;

        $template = [
            'first'    => '您好，您参加的拼团由于 ' . $this->order->cancel_reason . ',拼团失败',
            'keyword1' => $groupon->goods->name,
            'keyword2' => $groupon->price . '元',
            'keyword3' => $this->order->pay_status == 1 ? $groupon->price : '0' . '元',
            'remark'   => $this->order->pay_status == 1 ? $template_settings['remark'] : '感谢您的参与',
        ];
        $url      = app('system_setting')->getSetting('mobile_domain_url');
        $data     = [
            'template_id' => $template_settings['template_id'],
            'url'         => $url . '/#!/user/order/detail/' . $this->order->order_no,
            'touser'      => $this->getOpenId($user),
            'data'        => $template,
        ];

        return $data;
    }
}