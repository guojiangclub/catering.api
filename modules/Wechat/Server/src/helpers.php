<?php

if (!function_exists('wechat_channel')) {

    function wechat_channel()
    {
        return app('wechat.channel');
    }
}

/**
 * 小程序简易curl
 */
if (!function_exists('mini_curl')) {
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
}
