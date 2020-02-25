<?php

namespace GuoJiangClub\EC\Catering\Notifications;

use GuoJiangClub\EC\Catering\Notifications\Channels\Wechat;
use Illuminate\Bus\Queueable;

class GoodDeliver extends Notification
{
    use Queueable;

    protected $shipping;

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
        $this->shipping = $this->data['shipping'];
        if (empty($this->shipping)) {
            return false;
        }

        if ($this->checkOpenId($notifiable)) {
            return $this->getData($notifiable);
        }

        return false;
    }

    private function getData($user)
    {
        $template_settings = app('system_setting')->getSetting('wechat_message_deliver_goods_remind');
        if (empty($template_settings) || !isset($template_settings['status']) || $template_settings['status'] != 1) {
            return false;
        }

        $template = [
            'first'    => $template_settings['first'],
            'keyword1' => $this->shipping->method->name,
            'keyword2' => $this->shipping->tracking,
            'keyword3' => date('Y-m-d H:i', strtotime($this->shipping->created_at)),
            'remark'   => $template_settings['remark'],
        ];
        $url      = app('system_setting')->getSetting('mobile_domain_url');
        $data     = [
            'template_id' => $template_settings['template_id'],
            'url'         => $url . '/#!/user/order/express?name=' . $this->shipping->method->code . '&number=' . $this->shipping->tracking,
            'touser'      => $this->getOpenId($user),
            'data'        => $template,
        ];

        return $data;
    }
}
