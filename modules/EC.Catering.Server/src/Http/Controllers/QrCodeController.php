<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/19
 * Time: 12:56
 */

namespace ElementVip\Server\Http\Controllers;

use QrCode;

class QrCodeController extends Controller
{
    public function index()
    {
        if (request('url')) {
            //QrCode::format('png');
            return $this->api('data:image/png;base64,' . base64_encode(QrCode::format('png')
                    ->size(300)->generate(request('url'))));
        }

        return $this->api(null, false, 200, '请输入url');
    }
}