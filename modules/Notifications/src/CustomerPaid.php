<?php

namespace GuoJiangClub\EC\Catering\Notifications;

use GuoJiangClub\Catering\Component\User\Models\User;
use GuoJiangClub\EC\Catering\Notifications\Channels\Wechat;
use Illuminate\Bus\Queueable;

class CustomerPaid extends Notification
{
    use Queueable;

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
        $this->order = $this->data['order'];
        if (empty($this->order)) {
            return false;
        }

        if ($notifiable = $this->checkAdminOpenId($notifiable)) {
            return $this->getData($notifiable);
        }

        return false;
    }

    private function getData($user)
    {
        $template_settings = app('system_setting')->getSetting('wechat_message_customer_paid');
        if (empty($template_settings) || !isset($template_settings['status']) || $template_settings['status'] != 1) {
            return false;
        }

        $customer      = User::find($this->order->user_id);
        $customer_name = '';
        if ($customer->nick_name) {
            $customer_name = $customer->nick_name;
        } else {
            if ($customer->name) {
                $customer_name = $customer->name;
            }
        }

        $template = [
            'first'    => $template_settings['first'],
            'keyword1' => $this->order->order_no,
            'keyword2' => '在线支付',
            'keyword3' => ($this->order->total / 100) . '元',
            'keyword4' => $customer_name,
            'remark'   => $template_settings['remark'],
        ];
        //$url = app('system_setting')->getSetting('mobile_domain_url');
        $data = [
            'template_id' => $template_settings['template_id'],
            'url'         => '',
            'touser'      => $this->getOpenId($user),
            'data'        => $template,
        ];

        return $data;
    }
}
