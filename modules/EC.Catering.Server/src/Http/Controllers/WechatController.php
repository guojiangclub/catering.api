<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/6/22
 * Time: 16:16
 */

namespace GuoJiangClub\EC\Catering\Server\Http\Controllers;


use EasyWeChat\Factory;

class WechatController extends Controller
{
    public function getJsConfig()
    {
        if (env('JW_JSSDK')) {

            $res = $this->getJWJsConfig(base64_encode(request('url')));

            if (isset($res['success'])) {
                return [
                    "debug" => false,
                    "beta" => false,
                    "jsApiList" => [],
                    "appId" => $res['result']['appId'],
                    "nonceStr" => $res['result']['nonceStr'],
                    "timestamp" => $res['result']['timestamp'],
                    "url" => $res['result']['url'],
                    "signature" => $res['result']['signature'],
                    "rawString" => $res['result']['rawString'],
                ];
            }

            return null;
        }

        if (str_contains(settings('mobile_domain_url'), 'ibrand.cc')) {
            //return app('wechat.channel')->getJsConfig(request('url'), settings('wechat_app_id'));
        }

        if (!settings('wechat_app_id') OR !settings('wechat_app_secret')) {
            return [];
        }

        $options = [
            'app_id' => settings('wechat_app_id'),
            'secret' => settings('wechat_app_secret'),
        ];

        $app = Factory::officialAccount($options);;
        $js = $app->jssdk;

        $js->setUrl(urldecode(request('url')));

        return $js->buildConfig([]);
    }


    private function getJWJsConfig($callurl)
    {

        $url = 'https://jwscrm.mgcc.com.cn/mpapi/jssdk';

        $params = ['token' => env('JW_JSSDK_TOKEN'), 'callurl' => $callurl];

        return $this->Curl($url, $method = 'POST', $params);
    }

    private function Curl($url, $method = 'GET', $params = [], $request_header = [])
    {
        $request_header = ['Content-Type' => 'application/x-www-form-urlencoded'];
        if ($method === 'GET' || $method === 'DELETE') {
            $url .= (stripos($url, '?') ? '&' : '?') . http_build_query($params);
            $params = [];
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_header);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }
        $output = curl_exec($ch);
        curl_close($ch);

        return json_decode($output, true);
    }

}