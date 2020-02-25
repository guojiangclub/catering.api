<?php

namespace GuoJiangClub\EC\Catering\Notifications\Channels;

use GuoJiangClub\EC\Catering\Wechat\Server\Wx\AccessToken;
use Illuminate\Notifications\Notification;

class Wechat
{
    protected $appKey;
    protected $appUrl;
    protected $token;

    public function __construct()
    {
        $this->appKey = app('system_setting')->getSetting('wechat_app_id');
        $this->appUrl = app('system_setting')->getSetting('wechat_api_url');
        $this->token = new AccessToken($this->appUrl . 'oauth/token', 'wx_api',
            app('system_setting')->getSetting('wechat_api_client_id'),
            app('system_setting')->getSetting('wechat_api_client_secret'));
    }

    /**
     * 发送给定通知。
     *
     * @param  mixed $notifiable
     * @param  \Illuminate\Notifications\Notification $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->handle($notifiable);
        if ($message) {
            $this->wxCurl($this->appUrl . "api/notice/send?appid=" . $this->appKey, $message);
        }
    }

    private function wxCurl($url, $optData = null)
    {
        $headers[] = 'Authorization:Bearer ' . $this->token->getToken();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if (!empty($optData)) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($optData));
        }

        $res = curl_exec($ch);
        curl_close($ch);
        return json_decode($res);
    }

}
