<?php

namespace GuoJiangClub\EC\Catering\Notifications;

use GuoJiangClub\Catering\Component\Point\Repository\PointRepository;
use GuoJiangClub\EC\Catering\Notifications\Channels\Wechat;
use Illuminate\Bus\Queueable;

class PointChanged extends Notification
{
    use Queueable;

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

    protected $point;

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
        $template = [];

        $template_id = '9RXsgkTuMH-iqu5Q1ncpyuAzYVgTR2EYXv9B3xY3XG8';

        $userPoint = app(PointRepository::class)->getSumPointValid($user->id, 'default');

        //说明是积分兑换商品
        if ($this->point->action == 'order_point') {

            $template = [
                'first'    => ['value' => '亲爱的会员，感谢参与礼品换购活动！', "color" => "#173177"],
                'keyword1' => ['value' => '米尔童装积分商城', "color" => "#173177"],
                'keyword2' => ['value' => '礼品兑换', "color" => "#173177"],
                'keyword3' => ['value' => $this->point->value, "color" => "#173177"],
                'keyword4' => ['value' => $userPoint, "color" => "#173177"],
                'remark'   => ['value' => '点击了解详情', "color" => "#173177"],
            ];
        } elseif ($this->point->action == 'recharge_reward') {
            $template = [
                'first'    => ['value' => '亲爱的会员，您的积分有变动！', "color" => "#173177"],
                'keyword1' => ['value' => '米尔童装商城', "color" => "#173177"],
                'keyword2' => ['value' => '充值奖励', "color" => "#173177"],
                'keyword3' => ['value' => $this->point->value, "color" => "#173177"],
                'keyword4' => ['value' => $userPoint, "color" => "#173177"],
                'remark'   => ['value' => '点击了解详情', "color" => "#173177"],
            ];
        } else {
            $template = [
                'first'    => ['value' => '亲爱的会员，您的积分有变动！', "color" => "#173177"],
                'keyword1' => ['value' => '长沙高桥店', "color" => "#173177"],
                'keyword2' => ['value' => '消费购物', "color" => "#173177"],
                'keyword3' => ['value' => $this->point->value, "color" => "#173177"],
                'keyword4' => ['value' => $userPoint, "color" => "#173177"],
                'remark'   => ['value' => '点击了解详情', "color" => "#173177"],
            ];
        }

        $url = app('system_setting')->getSetting('mobile_domain_url');

        $data = [
            'touser'      => $this->getOpenId($user),
            'template_id' => $template_id,
            'url'         => $url . '/#!/user/point',
            'data'        => $template,
        ];

        return $data;
    }

}
