<?php

namespace GuoJiangClub\EC\Catering\Notifications;

use GuoJiangClub\EC\Catering\Notifications\Channels\Wechat;
use Illuminate\Bus\Queueable;
use GuoJiangClub\Catering\Component\Balance\Model\Balance;

class ChargeSuccess extends Notification
{
    use Queueable;

    protected $charge;

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
        $this->charge = $this->data['charge'];
        if (empty($this->charge)) {
            return false;
        }

        if ($this->checkOpenId($notifiable)) {
            return $this->getData($notifiable);
        }

        return false;
    }

    private function getData($user)
    {
        $template_settings = app('system_setting')->getSetting('wechat_message_charge_success');
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
            'keyword1' => ($this->charge['value'] / 100) . '元',
            'keyword2' => date('Y-m-d H:i'),
            'keyword3' => ($sum / 100) . '元',
            'remark'   => $template_settings['remark'],
        ];
        $url      = app('system_setting')->getSetting('mobile_domain_url');
        $data     = [
            'template_id' => $template_settings['template_id'],
            'url'         => $url . '/#!/recharge/balance/',
            'touser'      => $this->getOpenId($user),
            'data'        => $template,
        ];

        return $data;
    }
}
