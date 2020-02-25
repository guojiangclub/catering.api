<?php

namespace GuoJiangClub\EC\Catering\Notifications;

use GuoJiangClub\EC\Catering\Notifications\Channels\Wechat;
use Illuminate\Bus\Queueable;

class GrouponSuccess extends Notification
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
        $template_settings = app('system_setting')->getSetting('wechat_message_groupon_success');
        if (empty($template_settings) || !isset($template_settings['status']) || $template_settings['status'] != 1) {
            return false;
        }
        $groupon  = $this->grouponUser->belongsToGroupon;
        $template = [
            'first'    => '您参团的商品［' . $groupon->goods->name . '］已组团成功',
            'keyword1' => $groupon->price . '元',
            'keyword2' => $this->grouponUser->order->order_no,
            'remark'   => $template_settings['remark'],
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