<?php

namespace ElementVip\Server\Services;

use ElementVip\Wechat\Server\Wx\MiniAccessToken;
use Storage;

class MiniProgramService
{
    const API_WXACODE_GET = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=';//小程序接口B

    protected $token;

    public function __construct()
    {
        $app_id = app('system_setting')->getSetting('mini_program_app_id');
        $app_secret = app('system_setting')->getSetting('mini_program_secret');
        $this->token = new MiniAccessToken($app_id, $app_secret);
    }

    public function createMiniQrcode($page, $width, $scene = '', $type = 'share_order')
    {

        $img_name = $scene . '_' . $type . '_mini_qrcode.jpg';
        $savePath = $type . '/mini/qrcode/' . $img_name;
        if (Storage::disk('public')->exists($savePath)) {
            return $savePath;
        }

        $option = [
            'page' => $page,
            'width' => $width,
            'scene' => $scene,
        ];

        $body = $this->mini_curl(self::API_WXACODE_GET . $this->getAccessToken(), $option);

        if (str_contains($body, 'errcode')) {
            return false;
        }

        $result = Storage::disk('public')->put($savePath, $body);
        if ($result) {
            return $savePath;
        }

        return false;
    }

    function mini_curl($url, $optData = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        if (!empty($optData)) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($optData));
        }

        $res = curl_exec($ch);
        curl_close($ch);

        return $res;
    }

    protected function getAccessToken()
    {
        return $this->token->getToken();
    }

}