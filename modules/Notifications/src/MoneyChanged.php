<?php

namespace GuoJiangClub\EC\Catering\Notifications;

use GuoJiangClub\EC\Catering\Notifications\Channels\Wechat;
use Illuminate\Bus\Queueable;
use GuoJiangClub\Catering\Component\Balance\Model\Balance;

class MoneyChanged extends Notification
{
    use Queueable;

    protected $money;

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
        $this->money = $this->data['money'];
        if (empty($this->money)) {
            return false;
        }

        if ($this->checkOpenId($notifiable)) {
            return $this->getData($notifiable);
        }

        return false;
    }

    private function getData($user)
    {

        $template_settings = app('system_setting')->getSetting('wechat_message_money_changed');
        if (empty($template_settings) || !isset($template_settings['status']) || $template_settings['status'] != 1) {
            return false;
        }

        $sum = Balance::sumByUser($user->id);
        if (!is_numeric($sum)) {
            $sum = 0;
        } else {
            $sum = (int) $sum;
        }

        $template = [
            'first'    => $template_settings['first'],
            'keyword1' => date('Y-m-d H:i'),
            'keyword2' => $this->money['value'] . '元',
            'keyword3' => ($sum / 100) . '元',
            'remark'   => $template_settings['remark'],
        ];
        $url      = app('system_setting')->getSetting('mobile_domain_url');
        $data     = [
            'template_id' => $template_settings['template_id'],
            'url'         => $url . '/#!/user/wallet/index',
            'touser'      => $this->getOpenId($user),
            'data'        => $template,
        ];

        return $data;
    }
}
