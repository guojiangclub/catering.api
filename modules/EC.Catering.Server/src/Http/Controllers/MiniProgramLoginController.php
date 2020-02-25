<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/8
 * Time: 13:12
 */

namespace ElementVip\Server\Http\Controllers;

use ElementVip\Component\User\Models\User;
use ElementVip\Component\User\Models\UserBind;
use ElementVip\Wechat\Server\Wx\WXBizDataCrypt;
use Illuminate\Auth\Events\Login;
use RuntimeException;

class MiniProgramLoginController extends Controller
{
    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';
    const PATCH = 'PATCH';
    const DELETE = 'DELETE';

    const CODE_URL = 'https://api.weixin.qq.com/sns/jscode2session';

    public function login()
    {
        $app_id = settings('mini_program_app_id');
        $secret = settings('mini_program_secret');

        $type = request('app_type');
        if ($type == 'activity') {
            $app_id = settings('activity_mini_program_app_id');
            $secret = settings('activity_mini_program_secret');
        }

        $code = request('code');
        if (empty($app_id) OR empty($secret)) {
            return $this->response()->errorBadRequest('Please configure mini_program_app_id and mini_program_secret');
        }
        if (empty($code)) return $this->api([], false, 403, '缺失code');
        $params = [
            'appid' => $app_id,
            'secret' => $secret,
            'js_code' => $code,
            'grant_type' => 'authorization_code'
        ];
        $res = $this->Curl(self::CODE_URL, self::GET, $params);

        if (!isset($res['openid'])) {
            return $this->api([], false, 403, '获取open_id失败');
        }

        $openID = $res['openid'];
        $session_key = $res['session_key'];
        $cacheKey = $openID . '_mini_program_session_key';
        \Cache::forget($cacheKey);
        \Cache::put($cacheKey, $session_key, 10);

        $unionID = null;
        if (isset($res['unionid'])) {
            $unionID = $res['unionid'];
        }

        $userBind = UserBind::where('open_id', $openID)->where('type', 'miniprogram')->first();
        /*if ($userBind AND $user = User::find($userBind->user_id)) {*/
        if ($userBind AND $user = User::where('id', $userBind->user_id)->whereNotNull('mobile')->first()) {
            if ($unionID AND !$user->union_id) {
                $user->union_id = $unionID;
                $user->save();
            } elseif (!$unionID AND !$user->union_id) {
                return $this->api(['open_id' => $openID], true, 200, '');
            }

            if ($agent_code = request('agent_code')) {
                event('agent.user.relation', [$agent_code, $user->id, false]);
            }

            $token = $user->createToken($user->mobile)->accessToken;
            event(new Login($user, true));
            return response()
                ->json(['token_type' => 'Bearer', 'access_token' => $token, 'redirect_url' => '']);
        }

        return $this->api(['open_id' => $openID], true, 200, '');

    }

    public function unionIdLogin()
    {
        $app_id = settings('mini_program_app_id');

        $type = request('app_type');
        if ($type == 'activity') {
            $app_id = settings('activity_mini_program_app_id');
        }

        $iv = request('iv');
        $encryptedData = request('encryptedData');

        $openId = request('open_id');
        $sessionKey = \Cache::get($openId . '_mini_program_session_key');
        \Cache::forget($openId . '_mini_program_session_key');

        $pc = new WXBizDataCrypt($app_id, $sessionKey);
        $errCode = $pc->decryptData($encryptedData, $iv, $data);

        if ($errCode == 0) {
            $miniProgramResult = json_decode($data, true);
            \Log::info('MiniProgramResult:' . json_encode($miniProgramResult));

            $userBind = UserBind::where('open_id', $openId)->where('type', 'miniprogram')->first();

            if (isset($miniProgramResult['unionId'])) {  //如果有unionID
                $unionId = $miniProgramResult['unionId'];
                $userInfo = [
                    'gender' => $miniProgramResult['gender'],
                    'city' => $miniProgramResult['city'],
                    'avatarUrl' => $miniProgramResult['avatarUrl'],
                    'nickName' => $miniProgramResult['nickName'],
                    'unionID' => $unionId
                ];

                /*if ($user = User::where('union_id', $unionId)->first()) {*/
                if ($user = User::where('union_id', $unionId)->whereNotNull('mobile')->first()) {

                    if ($agent_code = request('agent_code')) {
                        event('agent.user.relation', [$agent_code, $user->id, false]);
                    }

                    $token = $user->createToken($user->mobile)->accessToken;
                    $this->bindUserInfo($user->id, $userInfo);
                    $this->bindOpenPlatform($user->id);
                    event(new Login($user, true));
                    return response()
                        ->json(['token_type' => 'Bearer', 'access_token' => $token, 'redirect_url' => '']);
                }


                /*if ($userBind AND $user = User::find($userBind->user_id)) {*/
                if ($userBind AND $user = User::where('id', $userBind->user_id)->whereNotNull('mobile')->first()) {

                    if ($agent_code = request('agent_code')) {
                        event('agent.user.relation', [$agent_code, $user->id, false]);
                    }

                    $this->bindUserInfo($user->id, $userInfo);
                    $token = $user->createToken($user->mobile)->accessToken;
                    event(new Login($user, true));
                    return response()
                        ->json(['token_type' => 'Bearer', 'access_token' => $token, 'redirect_url' => '']);
                } else {
                    return $this->api(['open_id' => $openId, 'union_id' => $unionId], true, 200, '');
                }

            } else { //没有unionID，说明没有绑定第三方平台，可以直接登录
                /*if ($userBind AND $user = User::find($userBind->user_id)) {*/
                if ($userBind AND $user = User::where('id', $userBind->user_id)->whereNotNull('mobile')->first()) {

                    if ($agent_code = request('agent_code')) {
                        event('agent.user.relation', [$agent_code, $user->id, false]);
                    }

                    $token = $user->createToken($user->mobile)->accessToken;
                    event(new Login($user, true));
                    return response()
                        ->json(['token_type' => 'Bearer', 'access_token' => $token, 'redirect_url' => '']);
                } else {
                    return $this->api(['open_id' => $openId, 'union_id' => ''], true, 200, '');
                }
            }

        } else {
            return $this->api([], false, 403, '获取union_id失败');
        }
    }


    public function MiniProgramMobileLogin()
    {
        if (is_null($model = config('auth.providers.users.model'))) {
            throw new RuntimeException('Unable to determine user model from configuration.');
        }

        $app_id = settings('mini_program_app_id');
        $secret = settings('mini_program_secret');

        $type = request('app_type');
        if ($type == 'activity') {
            $app_id = settings('activity_mini_program_app_id');
            $secret = settings('activity_mini_program_secret');
        }

        $unionID = request('union_id');
        $agent_code = request('agent_code');

        $encryptedData = request('encryptedData');
        $iv = request('iv');
        $is_new = false;

        $params = [
            'appid' => $app_id,
            'secret' => $secret,
            'js_code' => request('code'),
            'grant_type' => 'authorization_code'
        ];
        $res = $this->Curl(self::CODE_URL, self::GET, $params);
        if (!isset($res['session_key'])) {
            return $this->api([], false, 403, '获取session_key失败');
        }
        $sessionKey = $res['session_key'];


        $pc = new WXBizDataCrypt($app_id, $sessionKey);
        $errCode = $pc->decryptData($encryptedData, $iv, $data);

        if ($errCode == 0) {
            $getPhoneData = json_decode($data, true);
            $mobile = $getPhoneData['purePhoneNumber'];
            if (!$user = $model::where('mobile', $mobile)->first()) {
                /*for wan you*/
                if ($unionID AND $user = User::where('union_id', $unionID)->first()) {
                    $user->mobile = $mobile;
                    $user->save();
                } else {
                    $user = $model::create([
                        'mobile' => $mobile
                        , 'card_limit' => date('Y-m-d', time())
                        , 'group_id' => 1
                    ]);
                    $is_new = true;
                }
                /*for wan you end*/

                /*$user = $model::create([
                    'mobile' => $mobile
                    , 'card_limit' => date('Y-m-d', time())
                    , 'group_id' => 1
                ]);
                $is_new = true;*/
            }

            if ($agent_code) {
                event('agent.user.relation', [$agent_code, $user->id, $is_new]);
            }

            if ($unionID AND !$user->union_id) {
                $user->union_id = request('union_id');
                $user->save();
            }

            $token = $user->createToken($mobile)->accessToken;
            $this->bindOpenPlatform($user->id);
            event(new Login($user, true));

            return response()
                ->json(['token_type' => 'Bearer', 'access_token' => $token, 'redirect_url' => '', 'is_new_user' => $is_new]);
        } else {
            return $this->api([], false, 403, '获取手机号码失败');
        }


    }

    private function Curl($url, $method = self::GET, $params = [], $request_header = [])
    {
        $request_header = ['Content-Type' => 'application/x-www-form-urlencoded'];
        if ($method === self::GET || $method === self::DELETE) {
            $url .= (stripos($url, '?') ? '&' : '?') . http_build_query($params);
            $params = [];
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_header);
        if ($method === self::POST) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }
        $output = curl_exec($ch);
        curl_close($ch);

        return json_decode($output, true);
    }

    private function bindUserInfo($user_id, $userWxInfo)
    {
        $userInfo = [];
        if (!$user = User::find($user_id)) return;

        if (!$user->nick_name AND isset($userWxInfo['nickName'])) {
            $userInfo['nick_name'] = $userWxInfo['nickName'];
        }
        if (!$user->sex AND isset($userWxInfo['gender'])) {
            $userInfo['sex'] = $userWxInfo['gender'] == 1 ? '男' : '女';
        }
        if (!$user->avatar AND isset($userWxInfo['avatarUrl'])) {
            $userInfo['avatar'] = $userWxInfo['avatarUrl'];
        }
        if (!$user->city AND isset($userWxInfo['city'])) {
            $userInfo['city'] = $userWxInfo['city'];
        }
        if (!$user->union_id AND isset($userWxInfo['unionID'])) {
            $userInfo['union_id'] = $userWxInfo['unionID'];
        }

        if (count($userInfo) > 0) {
            $user->fill($userInfo);
            $user->save();
        }

    }

    private function bindOpenPlatform($userId)
    {
        $openId = request('open_id');
        $type = 'miniprogram';
        $app_id = settings('mini_program_app_id');

        $app_type = request('app_type');
        if ($app_type == 'activity') {
            $app_id = settings('activity_mini_program_app_id');
        }

        if (empty($openId) OR empty($type)) {
            return;
        }

        $userBind = UserBind::byOpenIdAndType($openId, $type)->first();

        if (empty($userBind)) {
            UserBind::create(['open_id' => $openId, 'type' => $type, 'user_id' => $userId, 'app_id' => $app_id]);
        } else {
            UserBind::bindUser($openId, $type, $userId);
        }

        $this->bindUserInfo($userId, request('userInfo'));
    }

    public function testGid()
    {
        $app_id = settings('mini_program_app_id');
        $secret = settings('mini_program_secret');
        $code = request('code');
        if (empty($app_id) OR empty($secret)) {
            return $this->response()->errorBadRequest('Please configure mini_program_app_id and mini_program_secret');
        }
        if (empty($code)) return $this->api([], false, 403, '缺失code');
        $params = [
            'appid' => $app_id,
            'secret' => $secret,
            'js_code' => $code,
            'grant_type' => 'authorization_code'
        ];
        $res = $this->Curl(self::CODE_URL, self::GET, $params);

        $encryptedData = request('encryptedData');
        $iv = urldecode(request('iv'));
        $sessionKey = $res['session_key'];
        $pc = new WXBizDataCrypt($app_id, $sessionKey);
        $errCode = $pc->decryptData($encryptedData, $iv, $data);
        \Log::info($errCode);
        if ($errCode == 0) {
            $data = json_decode($data, true);
            \Log::info($data);
            return $this->api($data);
        }

        return $this->api([], false, 200, '获取失败');
    }
}