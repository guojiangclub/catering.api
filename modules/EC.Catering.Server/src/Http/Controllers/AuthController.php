<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016-09-26
 * Time: 14:57
 */

namespace ElementVip\Server\Http\Controllers;


use ElementVip\Component\User\Models\User;
use ElementVip\Component\User\Models\UserBind;
use ElementVip\Server\Exception\UserExistsException;
use ElementVip\Wechat\Server\Wx\WXBizDataCrypt;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use League\OAuth2\Server\Exception\OAuthServerException;
use RuntimeException;
use Validator;
use Hash;
use iBrand\Sms\Facade as Sms;

class AuthController extends Controller
{
	const GET = 'GET';
	const POST = 'POST';
	const PUT = 'PUT';
	const PATCH = 'PATCH';
	const DELETE = 'DELETE';

	const CODE_URL = 'https://api.weixin.qq.com/sns/jscode2session';

	public function smsLogin()
	{
		$mobile = request('mobile');
		$code = request('code');
		$agent_code = request('agent_code');
		$agent_scan = request('agent_scan');
		$type = request('type');
		$open_id = request('open_id');
		$is_new = false;

		if (is_null($model = config('auth.providers.users.model'))) {
			throw new RuntimeException('Unable to determine user model from configuration.');
		}

		if (!empty($type) AND $type == 'register') {  //check if mobile exists
			if ($model::where('mobile', $mobile)->first()) {
				throw new UserExistsException();
			}
		}

		$credentials = [
			'mobile' => $mobile,
			'verifyCode' => $code,
		];

		//验证数据
		/*$validator = Validator::make($credentials, [
            'mobile' => 'required|confirm_mobile_not_change|confirm_rule:mobile_required',
            'verifyCode' => 'required|verify_code',
        ]);

        if ($validator->fails()) {
            throw OAuthServerException::invalidCredentials();
        }*/

		if (!Sms::checkCode($mobile, \request('code'))) {
			return $this->api('', false, 400, '验证码错误');
		}

		if (!$user = $model::where('mobile', $mobile)->first()) {
			/*for wan you*/
			if ($unionID = request('union_id') AND $user = User::where('union_id', $unionID)->first()) {
				$user->mobile = $mobile;
				$user->save();
			} else {
				$user   = $model::create([
					'mobile'     => $mobile,
					'card_limit' => date('Y-m-d', time()),
					'group_id'   => 1,
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

		if (request('union_id') AND !$user->union_id) {
			$user->union_id = request('union_id');
			$user->save();
		}

		if ($user->status == User::STATUS_FORBIDDEN) {
			return $this->api($user, false, 403, '您的账号已被禁用，联系网站管理员或客服！');
		}

		if (settings('market_member_barcode')) {
			event('market.on.user.login', [$user]);
		}

		// 小程序登录
		/*if ($type == 'miniprogram' && !empty($open_id)) {
            if (!UserBind::Where(['open_id' => $open_id, 'type' => $type])->update(['user_id' => $user->id])) {
                return $this->api($user, false, 403, '小程序登录失败');
            };
        }*/

		$token = $user->createToken($mobile)->accessToken;

		$this->bindOpenPlatform($user->id);

		//$redirect = $this->getWechatRedirectUrl($user->id);

		event(new Login($user, true));

		return response()
			->json(['token_type' => 'Bearer', 'access_token' => $token, 'redirect_url' => '', 'is_new_user' => $is_new]);
	}

	public function issueToken(Request $request)
	{
		$username = $request->input('username');
		$password = $request->input('password');

		if (is_null($model = config('auth.providers.users.model'))) {
			throw new RuntimeException('Unable to determine user model from configuration.');
		}

		switch ($username) {
			case isMobile($username):
				$user = $model::where(['mobile' => $username])->first();
				break;
			case isMail($username):
				$user = $model::where(['email' => $username])->first();
				break;
			case isLoginUsername($username):
				$user = $model::where(['name' => $username])->first();
				break;
			default:
				$user = false;
				break;

		}

		if (!$user) {
			return $this->failed('用户不存在');
		}

		if (!Hash::check($password, $user->password)) {
			return $this->failed('密码错误');
		}

		if ($user->status == User::STATUS_FORBIDDEN) {
			return $this->failed('您的账号已被禁用，联系网站管理员或客服！');
		}

		$token = $user->createToken($user->mobile)->accessToken;

		$this->bindOpenPlatform($user->id);

		//$redirect = $this->getWechatRedirectUrl($user->id);

		event(new Login($user, true));

		return response()
			->json(['token_type' => 'Bearer', 'access_token' => $token, 'redirect_url' => '']);
	}


	/**
	 * 小程序code获取open_id
	 */
	public function getOpenIdByCode()
	{
		$type = request('type');

		if ($type == 'activity') {
			$app_id = settings('activity_mini_program_app_id');
			$secret = settings('activity_mini_program_secret');
		} else {
			$app_id = settings('mini_program_app_id');
			$secret = settings('mini_program_secret');
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

		if (!isset($res['openid'])) return $this->api([], false, 403, '获取open_id失败');

		return $this->api(['openid' => $res['openid']], true, 200, '');

	}


	/**
	 * 微信小程序快捷登陆
	 *
	 */
	public function MiniProgramLogin()
	{

		if (is_null($model = config('auth.providers.users.model'))) {
			throw new RuntimeException('Unable to determine user model from configuration.');
		}

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

		if (!isset($res['openid'])) return $this->api([], false, 403, '获取open_id失败');
		$openId = $res['openid'];

		$userBind = UserBind::Where(['open_id' => $openId, 'type' => 'miniprogram'])->first();
		if (empty($userBind)) {
			/*UserBind::create(['open_id' => $openId, 'type' => 'miniprogram', 'app_id' => $app_id]);*/
			return $this->api(['open_id' => $openId], true, 200, '');
		}

		$user = $model::find($userBind->user_id);
		$token = $user->createToken($user->mobile)->accessToken;
		event(new Login($user, true));
		return response()
			->json(['token_type' => 'Bearer', 'access_token' => $token, 'redirect_url' => '']);

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


	/**
	 * 开放平台
	 */
	public function openPlatform()
	{
		$openId = request('open_id');
		$type = request('open_type');

		if (empty($openId) OR empty($type)) {
			return $this->response()->errorBadRequest('please set open_id and open_type fields');
		}

		$userBind = UserBind::byOpenIdAndType($openId, $type)->first();

		//1. 如果都存在则直接返回 token 给前端直接登录
		if ($userBind AND $userBind->user_id AND $user = User::find($userBind->user_id)) {

			$token = $user->createToken($user->mobile)->accessToken;

			event(new Login($user, true));

			return response()
				->json(['token_type' => 'Bearer', 'access_token' => $token]);
		}

		if (empty($userBind)) {
			UserBind::create(['open_id' => $openId, 'type' => $type, 'user_id' => request()->user()->id]);
		}

		return $this->api('', true);

	}

	public function bindWechat()
	{
		$openId = request('open_id');
		$type = request('open_type');

		if (empty($openId) OR empty($type)) {
			return $this->response()->errorBadRequest('please set open_id and open_type fields');
		}

		$userBind = UserBind::byOpenIdAndType($openId, $type)->first();

		if (empty($userBind)) {
			UserBind::create(['open_id' => $openId, 'type' => $type, 'user_id' => request()->user()->id]);
		}

		return $this->api('', true);
	}

	private function bindOpenPlatform($userId)
	{
		$openId = request('open_id');
		$type = request('open_type');
		$app_id = $type == 'wechat' ? settings('wechat_app_id') : settings('mini_program_app_id');

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

		$this->bindWxInfo($openId, $type, $userId);
	}

	private function getWechatRedirectUrl($userId)
	{
		if (request('open_type') == 'wechat') {
			if (!$userBind = UserBind::byUserIdAndType($userId, request('open_type'))->first()) {
				/*return 'http://wecom.ibrand.cc/wecom/' . env('WECHAT_APP_KEY') . '/oauth?redirect=';*/

				/*return 'http://wecom.ibrand.cc/oauth?appid=' . env('WECHAT_APP_KEY') . '&redirect=';*/
				return redirect(app('system_setting')->getSetting('wechat_api_url') . 'oauth?appid=' . settings('wechat_app_id') . '&redirect=');
			}
		}
		return '';
	}

	/**
	 * step 1 get
	 * @return mixed
	 */
	public function getOpenId()
	{
		$redirect = request('redirect_url') ? urlencode(request('redirect_url')) : env('MOBILE_USER_QUICKLOGIN');
		//return redirect(app('system_setting')->getSetting('wechat_api_url') . 'oauth?appid=' . env('WECHAT_APPID') . '&redirect=' . $redirect);

		return redirect(wechat_channel()->getOpenIdUrl() . $redirect);
	}

	public function wxSelectCoupon()
	{
		return redirect(app('system_setting')->getSetting('wechat_api_url') . 'oauth?appid=' . settings('wechat_app_id') . env('MOBILE_USER_SELECTCOUPON'));
	}

	/**
	 * step 2
	 */
	public function quickLogin()
	{
		$openId = request('open_id');
		$type = request('open_type');

		if (empty($openId) OR empty($type)) {
			return $this->response()->errorBadRequest('please set open_id and open_type fields');
		}

		/*获取UnionID，如果获取到UnionID，并且能够获取用户数据，登录*/
		$wxInfo = wechat_channel()->getUserInfo($openId);
		if (!isset($wxInfo->errcode) AND isset($wxInfo->unionid)) {
			if ($user = User::where('union_id', $wxInfo->unionid)->whereNotNull('mobile')->first()) {
				$token = $user->createToken($user->mobile)->accessToken;
				$this->bindOpenPlatform($user->id);
				event(new Login($user, true));
				return response()
					->json(['token_type' => 'Bearer', 'access_token' => $token, 'redirect_url' => '']);
			}
		}

		$userBind = UserBind::byOpenIdAndType($openId, $type)->first();

		//1. 如果都存在则直接返回 token 给前端直接登录
		if ($userBind AND $userBind->user_id AND $user = User::find($userBind->user_id) AND $user->mobile) {

			if ($user->status == User::STATUS_FORBIDDEN) {
				return $this->api($user, false, 403, '您的账号已被禁用，联系网站管理员或客服！');
			}

			$token = $user->createToken($user->mobile)->accessToken;

			event(new Login($user, true));

			$this->bindWxInfo($openId, $type, $user->id);

			if ($agent_code = request('agent_code')) {
				event('agent.user.relation', [$agent_code, $user->id, false]);
			}

			return response()
				->json(['token_type' => 'Bearer', 'access_token' => $token]);
		} else {
			return $this->api(['open_id' => $openId, 'open_type' => $type], false, 200, '用户不存在');
		}

	}

	/**
	 * 绑定微信用户信息
	 * @param $openID
	 * @param $user_id
	 * @param $type
	 */
	private function bindWxInfo($openID, $type, $user_id)
	{
		if (!$user = User::find($user_id)) {
			return;
		}
		$userInfo = [];

		if ($type == 'wechat') {
			$wxInfo = wechat_channel()->getUserInfo($openID);
			\Log::info('Get Wechat User Info:' . json_encode($wxInfo));
			if (!isset($wxInfo->errcode) && $wxInfo) {
				if (!$user->nick_name) {
					$userInfo['nick_name'] = $wxInfo->nickname;
				}
				if (!$user->sex) {
					$userInfo['sex'] = $wxInfo->sex == 1 ? '男' : '女';
				}
				if (!$user->avatar) {
					$userInfo['avatar'] = $wxInfo->headimgurl;
				}
				if (!$user->city) {
					$userInfo['city'] = $wxInfo->city;
				}

				if (!$user->union_id AND isset($wxInfo->unionid)) {
					$userInfo['union_id'] = $wxInfo->unionid;
				}

				if (count($userInfo) > 0) {
					$user->fill($userInfo);
					$user->save();
				}
			}
		} elseif ($type == 'miniprogram') {
			$userWxInfo = request('userInfo');
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

			if (count($userInfo) > 0) {
				$user->fill($userInfo);
				$user->save();
			}
		}

	}


	/**
	 * 小程序登录第一步：检测根据code拿到的openID信息里面，是否有unionID
	 * @return \Dingo\Api\Http\Response|void
	 */
	public function checkUnionID()
	{
		$app_id = settings('mini_program_app_id');
		$secret = settings('mini_program_secret');
		$code = request('code');
		if (empty($app_id) OR empty($secret)) {
			return $this->response()->errorBadRequest('Please configure mini_program_app_id and mini_program_secret');
		}
		$params = [
			'appid' => $app_id,
			'secret' => $secret,
			'js_code' => $code,
			'grant_type' => 'authorization_code'
		];
		$res = $this->Curl(self::CODE_URL, self::GET, $params);
		\Log::info('checkUnionID:' . json_encode($res));
		if (isset($res['errcode'])) {
			return $this->api([], false, 403, '请求失败');
		}

		//将数据存入Cache，方便下个接口使用
		$session_key = $res['session_key'];
		$openId = $res['openid'];
		$cacheKey = $openId . '_mini_program_session_key';
		\Cache::forget($cacheKey);
		\Cache::put($cacheKey, $session_key, 10);

		if (!isset($res['unionid'])) {
			return $this->api(['has_unionid' => false, 'open_id' => $openId]);
		}

		$unionId = $res['unionid'];
		return $this->api(['has_unionid' => true, 'union_id' => $unionId, 'open_id' => $openId]);
	}

	/**
	 * UNIONID快捷登录机制
	 * @return mixed
	 */
	public function UnionIDQuickLogin()
	{
		if (is_null($model = config('auth.providers.users.model'))) {
			throw new RuntimeException('Unable to determine user model from configuration.');
		}

		$openId = request('open_id');
		$type = request('open_type');
		$app_id = $type == 'wechat' ? settings('wechat_app_id') : settings('mini_program_app_id');
		$userInfo = [];
		//如果小程序前端传了encryptedData,iv，那么去获取用户信息，主要是为了获取unionID
		if ($type == 'miniprogram' AND $iv = request('iv') AND $encryptedData = request('encryptedData')) {
			$sessionKey = \Cache::get($openId . '_mini_program_session_key');
			\Cache::forget($openId . '_mini_program_session_key');

			$pc = new WXBizDataCrypt($app_id, $sessionKey);
			$errCode = $pc->decryptData($encryptedData, $iv, $data);

			if ($errCode == 0) {
				$miniProgramResult = json_decode($data, true);
				\Log::info('$miniProgramResult:' . json_encode($miniProgramResult));
				$unionId = $miniProgramResult['unionId'];
				$userInfo = [
					'gender' => $miniProgramResult['gender'],
					'city' => $miniProgramResult['city'],
					'avatarUrl' => $miniProgramResult['avatarUrl'],
					'nickName' => $miniProgramResult['nickName']
				];
			} else {
				return $this->api([], false, 403, '获取union_id失败');
			}
		} elseif ($type == 'miniprogram' AND request('union_id')) {
			$unionId = request('union_id');
			$user_info = request('user_info');
			$userInfo = [
				'gender' => $user_info['gender'],
				'city' => $user_info['city'],
				'avatarUrl' => $user_info['avatarUrl'],
				'nickName' => $user_info['nickName']
			];

		} else { //微信环境
			$userInfo = $this->getWechatUserInfo($openId);
			$unionId = isset($userInfo['union_id']) ? $userInfo['union_id'] : '';
		}

		if (!$unionId) {
			return $this->api([], false, 403, '缺失union_id');
		}

		$user = $model::where('union_id', $unionId)->first();
		$userBind = UserBind::Where(['open_id' => $openId, 'type' => $type])->first();
		$new_user = false;

		if ($user) { //如果unionId获取到用户，直接登录
			//

		} elseif ($userBind AND $user = $model::find($userBind->user_id)) { //如果用户没有绑定unionID
			$user->union_id = $unionId;
			$user->save();
		} else {    //全新用户
			$user = $model::create([
				'union_id' => $unionId
				, 'card_limit' => date('Y-m-d', time())
				, 'group_id' => 1
			]);
			$new_user = true;
		}
		$this->bindUserInfo($user->id, $userInfo);

		if (!$userBind) {
			UserBind::create(['open_id' => $openId, 'type' => $type, 'user_id' => $user->id, 'app_id' => $app_id]);
		} else {
			UserBind::bindUser($openId, $type, $user->id);
		}

		if ($agent_code = request('agent_code')) {
			event('agent.user.relation', [$agent_code, $user->id, $new_user]);
		}

		$token = $user->createToken($unionId)->accessToken;
		event(new Login($user, true));
		return response()
			->json(['token_type' => 'Bearer', 'access_token' => $token, 'redirect_url' => '']);
	}


	/**
	 * 微信登录，获取微信用户信息，主要为了获取UnionID
	 * @return \Dingo\Api\Http\Response
	 */
	protected function getWechatUserInfo($open_id)
	{
		$userInfo = [];
		$wxInfo = wechat_channel()->getUserInfo($open_id);
		\Log::info('get wechat user info1:' . json_encode($wxInfo));
		if (!$wxInfo) {
			$wxInfo = wechat_channel()->getUserInfo($open_id, true);
			\Log::info('get wechat user info2:' . json_encode($wxInfo));
		}

		if ($wxInfo AND !isset($wxInfo->errcode)) {
			$userInfo['nickName'] = $wxInfo->nickname;
			$userInfo['gender'] = $wxInfo->sex;
			$userInfo['avatarUrl'] = $wxInfo->headimgurl;
			$userInfo['city'] = $wxInfo->city;
			if (isset($wxInfo->unionid)) {
				$userInfo['union_id'] = $wxInfo->unionid;
			}
		}
		return $userInfo;
	}

	/**
	 * 绑定用户微信信息
	 * @param $openID
	 */
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

//        if (!$user->union_id) {
//            $userInfo['union_id'] = request('union_id');
//        }

		if (count($userInfo) > 0) {
			$user->fill($userInfo);
			$user->save();
		}

	}

	/**
	 * 微信环境登陆，获取跳转链接
	 * @return \Dingo\Api\Http\Response
	 */
	public function getRedirectUrl()
	{
		if ($appID = settings('wechat_app_id')) {
			$redirect = request('redirect_url') ? urlencode(request('redirect_url')) : env('MOBILE_USER_QUICKLOGIN');
			$url = app('system_setting')->getSetting('wechat_api_url') . 'oauth?appid=' . $appID . '&redirect=' . $redirect;
			return $this->api(['url' => $url]);
		}
		return $this->api();
	}

	/**
	 * 不使用手机号码登录（for jw）
	 * @return \Dingo\Api\Http\Response|void
	 */
	public function quickLoginEmptyMobile()
	{
		$openId = request('open_id');
		$type = request('open_type');

		if (is_null($model = config('auth.providers.users.model'))) {
			throw new RuntimeException('Unable to determine user model from configuration.');
		}

		if (empty($openId) OR empty($type)) {
			return $this->response()->errorBadRequest('please set open_id and open_type fields');
		}

		/*获取UnionID，如果获取到UnionID，并且能够获取用户数据，登录*/
		$wxInfo = wechat_channel()->getUserInfo($openId);
		if (!isset($wxInfo->errcode) AND isset($wxInfo->unionid)) {
			if ($user = User::where('union_id', $wxInfo->unionid)->whereNotNull('mobile')->first()) {
				$token = $user->createToken($user->union_id)->accessToken;
				$this->bindOpenPlatform($user->id);
				event(new Login($user, true));
				return response()
					->json(['token_type' => 'Bearer', 'access_token' => $token, 'redirect_url' => '']);
			}
		}

		$userBind = UserBind::byOpenIdAndType($openId, $type)->first();

		//1. 如果都存在则直接返回 token 给前端直接登录
		if ($userBind AND $userBind->user_id AND $user = User::find($userBind->user_id)) {

			if ($user->status == User::STATUS_FORBIDDEN) {
				return $this->api($user, false, 403, '您的账号已被禁用，联系网站管理员或客服！');
			}

			$token = $user->createToken($userBind->open_id)->accessToken;

			event(new Login($user, true));

			$this->bindWxInfo($openId, $type, $user->id);

			if ($agent_code = request('agent_code')) {
				event('agent.user.relation', [$agent_code, $user->id, false]);
			}

			return response()
				->json(['token_type' => 'Bearer', 'access_token' => $token]);
		} else {
			$user = $model::create([
				'card_limit' => date('Y-m-d', time())
				, 'group_id' => 1
			]);

			$token = $user->createToken($openId)->accessToken;

			$this->bindOpenPlatform($user->id);

			event(new Login($user, true));

			if ($agent_code = request('agent_code')) {
				event('agent.user.relation', [$agent_code, $user->id, false]);
			}

			return response()
				->json(['token_type' => 'Bearer', 'access_token' => $token, 'redirect_url' => '', 'is_new_user' => true]);
		}

	}
}
