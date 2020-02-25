<?php

namespace GuoJiangClub\EC\Catering\Wechat\Server\Wx;

use GuoJiangClub\EC\Catering\Wechat\Server\Wx\Wx;

/**
 * 微信接口
 */
class JSSDK
{

    protected static $appID;
    protected        $wx;

    public function __construct(Wx $wx)
    {
        self::$appID = settings('wechat_app_id');
        $this->wx    = $wx;
    }

    public function getSignPackage()
    {
//        $jsapiTicket = $this->getTicket();
        $jsapiTicket = $this->wx->getJsTicket();
        \Log::info('ticket' . $jsapiTicket);
        $url       = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $url       = route('user.test');
        $timestamp = time();
        $nonceStr  = $this->createNonceStr();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        $signPackage = [
            "appId"     => self::$appID,
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string,
        ];

        return $signPackage;
    }

    protected function getTokenFromWx()
    {
        $token = new AccessToken('https://api.weixin.qq.com/cgi-bin/token', 'wechat_api', settings('wechat_app_id'), env('WECHAT_APP_SECRET'));

        return $token->getTokenFromWx();
    }

    public function getTicket()
    {
        $token  = $this->getTokenFromWx();
        $ticket = new AccessToken('https://api.weixin.qq.com/cgi-bin/ticket/getticket', 'wechat_api_getticket', '', $token);

        return $ticket->getTicketFromWx();
    }

    private function createNonceStr($length = 16)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str   = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }

        return $str;
    }

}
