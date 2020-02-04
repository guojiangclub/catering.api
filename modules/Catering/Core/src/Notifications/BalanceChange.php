<?php

namespace GuoJiangClub\Catering\Core\Notifications;

use ElementVip\Notifications\Channels\Wechat;
use ElementVip\Notifications\Notification;
use Illuminate\Bus\Queueable;
use GuoJiangClub\Catering\Component\Balance\Model\Balance;

class BalanceChange extends Notification
{
    use Queueable;

    protected $balance;

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
        $this->balance = $this->data['balance'];
        if (empty($this->balance)) {
            return false;
        }

        if ($this->checkOpenId($notifiable)) {
            return $this->getData($notifiable);
        }

        return false;
    }

    private function getData($user)
    {

        $template_settings = app('system_setting')->getSetting('wechat_message_st_balance_changed');
        if (empty($template_settings) || !isset($template_settings['status']) || $template_settings['status'] != 1) {
            return false;
        }

        $sum = Balance::sumByUser($user->id);
        if (!is_numeric($sum)) {
            $sum = 0;
        } else {
            $sum = (int)$sum;
        }

        $template = [
            'first' => $template_settings['first'],
            'keyword1' => $this->balance['note'],  //交易类型
            'keyword2' => $this->balance['value'] / 100 . '元',  //交易金额
            'keyword3' => $this->balance['time'],  //交易时间
            'keyword4' => ($sum / 100) . '元',  //账户余额
            'remark' => $template_settings['remark'],
        ];

        $data = [
            'template_id' => $template_settings['template_id'],
            'url' => '',
            'touser' => $this->getOpenId($user),
            'data' => $template,
        ];

        $data["miniprogram"] = [
            "appid" => env('SHITANG_MINI_PROGRAM_APPID'),
            "pagepath" => 'pages/member/myMoney/myMoney'
        ];

        return $data;
    }
}
