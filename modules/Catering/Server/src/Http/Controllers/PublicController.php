<?php
/**
 * Created by PhpStorm.
 * User: eddy
 * Date: 2019/7/20
 * Time: 23:12
 */

namespace GuoJiangClub\Catering\Server\Http\Controllers;

use GuoJiangClub\Catering\Component\Product\Models\Goods;
use GuoJiangClub\Catering\Component\User\Models\Group;
use GuoJiangClub\Catering\Component\User\Models\UserBind;
use GuoJiangClub\Catering\Component\User\Repository\UserBindRepository;
use GuoJiangClub\Catering\Component\User\Repository\UserRepository;
use EasyWeChat;
use GuoJiangClub\Catering\Core\Notifications\JoinSuccess;

class PublicController extends Controller
{
	protected $userRepository;
	protected $userBindRepository;

	public function __construct(UserRepository $userRepository
		, UserBindRepository $userBindRepository)
	{
		$this->userRepository     = $userRepository;
		$this->userBindRepository = $userBindRepository;
	}

	public function GettingUserProtocol()
	{
		$protocol = settings('member_rules_link');

		return $this->success(['protocol' => $protocol]);
	}

	public function authorizationMobile()
	{
		$miniProgram = EasyWeChat::miniProgram('shitang');

		//1. get session key.
		$code   = request('code');
		$result = $miniProgram->auth->session($code);

		if (!isset($result['session_key'])) {
			return $this->failed('获取 session_key 失败.');
		}

		$sessionKey = $result['session_key'];

		//2. get phone number.
		$encryptedData = request('encryptedData');
		$iv            = request('iv');

		$decryptedData = $miniProgram->encryptor->decryptData($sessionKey, $iv, $encryptedData);

		if (!isset($decryptedData['purePhoneNumber'])) {
			return $this->failed('获取手机号失败.');
		}

		$mobile = $decryptedData['purePhoneNumber'];

		return $this->success(['mobile' => $mobile]);
	}

	public function register()
	{
		$mobile = request('mobile');
		$openId = request('open_id');
		$user   = $this->userRepository->getUserByCredentials(['mobile' => $mobile]);
		if (!$user) {
			$code  = $this->generate_code();
			$data  = ['mobile' => $mobile, 'card_no' => create_member_card_no(), 'confirmation_code' => $code];
			$group = Group::orderBy('grade', 'ASC')->first();
			if ($group) {
				$data['group_id'] = $group->id;
			}

			$user = $this->userRepository->create($data);
		}

		$app_id = env('SHITANG_MINI_PROGRAM_APPID');
		$params = [
			'appid'      => $app_id,
			'secret'     => env('SHITANG_MINI_PROGRAM_SECRET'),
			'js_code'    => request('code'),
			'grant_type' => 'authorization_code',
		];

		$res = $this->Curl('https://api.weixin.qq.com/sns/jscode2session', 'GET', $params);
		\Log::info([$res]);
		if (isset($res['errcode'])) {
			return $this->failed('请求失败，请重试');
		}

		$unionid = '';
		if (isset($res['unionid'])) {
			$unionid = $res['unionid'];
			\Log::info('unionid:' . $unionid);
			$this->updateWechatUserBind($unionid, $user->id);
		}

		$this->bindOpenPlatform($user->id, $openId, $unionid);
		$this->bindWxInfo($user);

		event('st.user.generate.qrcode', [$user]);
		event('user.register.newUserGift', [$user]);

		if (request('agent_code')) {
			event('user.register.agent.share', [$user, request('agent_code')]);
		}

		$token = $user->createToken($user->id)->accessToken;

		$user->notify(new JoinSuccess([]));

		return $this->success(['token_type' => 'Bearer', 'access_token' => $token]);
	}

	private function bindOpenPlatform($userId, $openId = '', $unionid = '')
	{
		$type   = 'miniprogram';
		$app_id = config('wechat.mini_program.shitang.app_id');

		if (empty($openId)) {
			return;
		}

		$userBind = UserBind::where('open_id', $openId)->first();
		if ($userBind) {
			$userBind->unionid = $unionid;
			$userBind->user_id = $userId;
			$userBind->save();
		} else {
			UserBind::create(['open_id' => $openId, 'type' => $type, 'user_id' => $userId, 'app_id' => $app_id, 'unionid' => $unionid]);
		}
	}

	private function updateWechatUserBind($unionid, $user_id)
	{
		$userBind = UserBind::where('unionid', $unionid)->where('type', 'wechat')->first();
		if ($userBind) {
			$userBind->user_id = $user_id;
			$userBind->save();
		}
	}

	private function bindWxInfo($user)
	{
		$userInfo   = [];
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

	public function getPointGoods()
	{
		$goods = Goods::where('is_largess', 1)->where('is_del', 0)->orderBy('sort', 'desc')->take(2)->get();

		return $this->success(['pointGoods' => $goods]);
	}

	protected function generate_code()
	{
		$code  = '';
		$check = true;
		while ($check) {
			$code = generate_random_string();
			$user = $this->userRepository->getUserByCredentials(['confirmation_code' => $code]);
			if (!$user) {
				$check = false;
			}
		}

		return $code;
	}

	private function Curl($url, $method = 'GET', $params = [], $request_header = [])
	{
		$request_header = ['Content-Type' => 'application/x-www-form-urlencoded'];
		if ($method === 'GET' || $method === 'DELETE') {
			$url    .= (stripos($url, '?') ? '&' : '?') . http_build_query($params);
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