<?php

/**
 * 44. * isMobile函数:检测参数的值是否为正确的中国手机号码格式
 * 45. * 返回值:是正确的手机号码返回手机号码,不是返回false
 * 46. */
function isMobile($Argv)
{
    $RegExp = '/^(\+?0?86\-?)?((13\d|14[57]|15[^4,\D]|17[678]|18\d)\d{8}|170[059]\d{7})$/';
    return preg_match($RegExp, $Argv) ? $Argv : false;
}

/**
 * isMail函数:检测是否为正确的邮件格式
 * 返回值:是正确的邮件格式返回邮件,不是返回false
 */
function isMail($Argv)
{
    $RegExp = '/^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/';
    return preg_match($RegExp, $Argv) ? $Argv : false;
}

function isLoginUsername($Argv)
{
    //$RegExp = '/^[a-zA-Z\x{4e00}-\x{9fa5}]{2,20}$/u';
    $RegExp = '/^[a-zA-Z\d\x{4e00}-\x{9fa5}]{2,20}$/u';
    return preg_match($RegExp, $Argv) ? $Argv : false;
}


function isWechat()
{
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {

        return true;

    }
    return false;
}


function isNumber($Argv)
{
    $RegExp = '/^[0-9]*$/';
    return preg_match($RegExp, $Argv) ? $Argv : false;
}


if (!function_exists('api_route')) {
    /**
     * Generate the URL to a named route.
     *
     * @param  string $name
     * @param  array $parameters
     * @param  bool $absolute
     * @return string
     */
    function api_route($name, $parameters = [], $absolute = true)
    {
        $url = app('Dingo\Api\Routing\UrlGenerator')->version('v1')->route($name, $parameters, $absolute);

        if (env('SECURE') AND !str_contains($url, 'https')) {
            return str_replace('http', 'https', $url);
        }

        return $url;
    }
}


if (!function_exists('collect_to_array')) {
    /**
     * Generate the URL to a named route.
     *
     * @param  string $name
     * @param  array $parameters
     * @param  bool $absolute
     * @return string
     */
    function collect_to_array($collection)
    {
        $array = [];
        foreach ($collection as $item) {
            $array[] = $item;
        }
        return $array;
    }
}


if (!function_exists('str2hex')) {
    /**
     * Generate the str to 16hex.
     *
     * @param  string $str
     * @param  string $type //bold:加粗 08 ;high:加高 10; width:加宽 20; disStyle:取消样式 00
     * @param  bool $center center:居中1B 61 1;
     */
    function str2hex($str, $center = false, $bold = false)
    {
        $str = iconv('utf-8', 'gbk', $str);
        $hex = '';

        for ($i = 0, $length = strlen($str); $i < $length; $i++) {
            $hex .= dechex(ord($str{$i}));
        }

        //$array = ['20', '20', '20', '20'];

        /*添加样式*/
        if ($center) {  //居中
            $array[] = '1B';
            $array[] = '61';
            $array[] = '1';
        }

        if ($bold) {  //字体放大
            $array[] = '1B';
            $array[] = '21';
            $array[] = '18';
        }
        /*end添加样式*/

        for ($start = 0; $start < strlen($hex); $start += 2) {
            $array[] = substr($hex, $start, 2);
        }

        /*取消样式*/
        if ($bold) {  //字体放大
            $array[] = '1B';
            $array[] = '21';
            $array[] = '0';
        }

        $array[] = '0a';
        if ($center) {  //居中
            $array[] = '1B';
            $array[] = '61';
            $array[] = '0';

        }
        /*end取消样式*/

        return $array;
    }
}

function setPintStyle($item)
{
    if ($item == 'bold') {
        return '08';
    } elseif ($item == 'high') {
        return '10';
    } elseif ($item == 'width') {
        return '20';
    }
    return '';
}

if (!function_exists('create_member_card_no')) {
    function create_member_card_no($star = '1000000', $end = '9000000')
    {
        return '8' . rand($star, $end);
    }
}