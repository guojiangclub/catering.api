<?php
namespace ElementVip\Server\Http\Controllers;

use Illuminate\Http\Request;
use SmsManager as Manager;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016-08-19
 * Time: 12:28
 */
class SmsController extends Controller
{
    public function postVoiceVerify(Request $request)
    {
        $mobile = $request->input('mobile', null);
        $interval = $request->input('interval', 60);

        $res = Manager::validateSendable($interval);
        if (!$res['success']) {
            return response()->json($res);
        }
        $res = Manager::validateFields($request->all());
        if (!$res['success']) {
            return response()->json($res);
        }
        $res = Manager::requestVoiceVerify($mobile, $interval);
        return response()->json($res);
    }

    public function postSendCode(Request $request)
    {
        $mobile = $request->input('mobile', null);
        $interval = $request->input('interval', 60);

        $res = Manager::validateSendable($interval);
        if (!$res['success']) {
            return response()->json($res);
        }
        $res = Manager::validateFields($request->all());
        if (!$res['success']) {
            return response()->json($res);
        }
        $res = Manager::requestVerifySms($mobile, $interval);

        /*return response()->json($res);*/
        return response($res);
    }
}