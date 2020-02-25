<?php

namespace GuoJiangClub\EC\Catering\Notifications;

use GuoJiangClub\EC\Catering\Notifications\Channels\Wechat;
use Illuminate\Bus\Queueable;
use GuoJiangClub\Catering\Component\Point\Model\Point;

class PointRecord extends Notification
{
    use Queueable;

    protected $point;

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
        $this->point = $this->data['point'];
        if (empty($this->point)) {
            return false;
        }

        if ($this->checkOpenId($notifiable)) {
            return $this->getData($notifiable);
        }

        return false;
    }

    private function getData($user)
    {
        $template_settings = app('system_setting')->getSetting('wechat_message_point_changed');
        if (empty($template_settings) || !isset($template_settings['status']) || $template_settings['status'] != 1) {
            return false;
        }

        $user_name = '';
        if ($user->nick_name) {
            $user_name = $user->nick_name;
        } else {
            if ($user->name) {
                $user_name = $user->name;
            }
        }

        $sum      = Point::where('user_id', $user->id)->valid()->sumPoint();
        $template = [
            'first'    => $template_settings['first'],
            'keyword1' => $user_name,
            'keyword2' => $user->card_no,
            'keyword3' => $this->point['note'] . ': ' . $this->point['value'] . ',当前可用积分: ' . $sum,
            'remark'   => $template_settings['remark'],
        ];
        $url      = app('system_setting')->getSetting('mobile_domain_url');
        $data     = [
            'template_id' => $template_settings['template_id'],
            'url'         => $url . '/#!/user/point',
            'touser'      => $this->getOpenId($user),
            'data'        => $template,
        ];

        return $data;
    }
}
