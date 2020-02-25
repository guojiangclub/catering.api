<?php

namespace GuoJiangClub\EC\Catering\Wechat\Server\Wx;

class MiniGetWxaCode
{
    protected $token;
    const API_WXACODE_GET_A = 'https://api.weixin.qq.com/wxa/getwxacode?access_token=';
    const API_WXACODE_GET_B = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token='; //小程序接口B

    public function __construct()
    {
        $this->token = new MiniAccessToken(settings('mini_program_app_id'), settings('mini_program_secret'));
    }

    protected function getAccessToken()
    {
        return $this->token->getToken();
    }

    public function createMiniQrcode($page, $width, $save_path, $type = 'B', $scene = '')
    {
        $option['width'] = $width;
        if ($type == 'A') {
            $option['path'] = $page;
        } else {
            $option['page'] = $page;
        }

        if ($type == 'B') {
            $option['scene'] = $scene;
            $body            = mini_curl(self::API_WXACODE_GET_B . $this->getAccessToken(), $option);
        } else {
            $body = mini_curl(self::API_WXACODE_GET_A . $this->getAccessToken(), $option);
        }
        if (str_contains($body, 'errcode')) {
            return false;
        }

        $result = \Storage::put($save_path, $body);
        if ($result) {
            return true;
        }

        return false;
    }

}