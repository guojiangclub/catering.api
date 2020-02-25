<?php

namespace GuoJiangClub\EC\Catering\Wechat\Server\Wx;

/**
 * 微信接口
 */
class Wx
{

    protected static $appkey;
    protected static $appUrl;
//    protected static $appkey = 'wxdea745d4f3fc823c';
//    protected static $appUrl = 'http://wechat.test.ibd.so/';

    //protected static $appkey = 'wxf1390c5456aaf77a'; // TNF 正式公号

    protected static $templateMessageType = [];

    protected $token;

    protected $http;

    public function __construct()
    {
        self::$appkey = settings('wechat_app_id');
        self::$appUrl = app('system_setting')->getSetting('wechat_api_url');

        $this->token = new AccessToken(self::$appUrl . 'oauth/token', 'wx_api', app('system_setting')->getSetting('wechat_api_client_id'), app('system_setting')->getSetting('wechat_api_client_secret'));
        $this->http  = new Http($this->token);
    }

    /**
     * 生成获取Openid的url
     *
     * @return type
     */
    public function getOpenIdUrl()
    {
        return self::$appUrl . 'oauth?appid=' . self::$appkey . '&redirect=';
    }

    /**
     * 授权获取用户微信信息
     *
     * @param type $openid
     *
     * @return type
     */
    public function getUserInfo($openid, $forceRefresh = false)
    {

        return $this->wxCurl(self::$appUrl . "api/oauth/user?appid=" . self::$appkey . "&openid=" . $openid, null, $forceRefresh);
    }

    public function userInfo($openid, $forceRefresh = false)
    {
        return $this->wxCurl(self::$appUrl . "api/fans/get?appid=" . self::$appkey, ['openid' => $openid], $forceRefresh);
    }

    public function getJsConfig($url, $appid = null)
    {
        if ($appid) {
            return $this->wxCurl(self::$appUrl . "api/js/config?appid=" . $appid . "&url=" . $url);
        }

        return $this->wxCurl(self::$appUrl . "api/js/config?appid=" . self::$appkey . "&url=" . $url);
    }

    public function TemplateMessage($url, $data)
    {
        return $this->wxCurl($url, $data);
    }

    /**
     * 上传图片
     *
     * @param $path
     *
     * @return type
     */
    public function upload($path)
    {
        $image     = curl_file_create($path);
        $url       = self::$appUrl . "api/card/upload/image?appid=" . self::$appkey;
        $data      = [
            'image' => $image,
        ];
        $headers[] = 'Authorization:Bearer ' . $this->token->getToken();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

//        curl_setopt($ch, CURLOPT_URL, $url);
//
//        curl_setopt($ch, CURLOPT_POST, true );
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
//        curl_setopt($ch, CURLOPT_HEADER, false);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        curl_close($ch);

        return $response;
    }

    /* 内置函数 */

    /**
     * 微信简易curl
     *
     * @param type $url
     * @param type $optData
     *
     * @return type
     */
    private function wxCurl($url, $optData = null, $forceRefresh = false)
    {

        $headers[] = 'Authorization:Bearer ' . $this->token->getToken($forceRefresh);

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
