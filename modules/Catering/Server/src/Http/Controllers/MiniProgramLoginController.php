<?php

namespace GuoJiangClub\Catering\Server\Http\Controllers;

use GuoJiangClub\Catering\Component\User\Models\UserBind;
use GuoJiangClub\Catering\Component\User\Repository\UserBindRepository;
use GuoJiangClub\Catering\Component\User\Repository\UserRepository;
use EasyWeChat;

class MiniProgramLoginController extends Controller
{
	protected $userRepository;
	protected $userBindRepository;
	protected $marketService;

	public function __construct(UserRepository $userRepository
		, UserBindRepository $userBindRepository)
	{
		$this->userRepository     = $userRepository;
		$this->userBindRepository = $userBindRepository;
	}

	public function login()
	{
		if (is_null($model = config('auth.providers.users.model'))) {
			throw new RuntimeException('Unable to determine user model from configuration.');
		}

		$code = request('code');
		if (empty($code)) {
			return $this->failed('缺失code');
		}

		$miniProgram = EasyWeChat::miniProgram('shitang');

		$result = $miniProgram->auth->session($code);

		if (!isset($result['openid'])) {
			return $this->failed('获取openid失败.');
		}

		$openid    = $result['openid'];
		$user_info = null;

		$userBind = UserBind::where('open_id', $openid)->where('type', 'miniprogram')->first();
		if (!$userBind) {
			return $this->success(['open_id' => $openid]);
		}

		$user = $this->userRepository->findWhere(['id' => $userBind->user_id])->first();
		if ($user && $user->mobile) {
			$token = $user->createToken($user->mobile)->accessToken;
			event('st.user.generate.qrcode', [$user]);

			return $this->success(['token_type' => 'Bearer', 'access_token' => $token]);
		}

		return $this->success(['open_id' => $openid]);
	}

	public function mobileLogin()
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

		//3. get or create user.
		if (!$user = $this->userRepository->getUserByCredentials(['mobile' => $mobile])) {
			$user = $this->userRepository->create(['mobile' => $mobile]);
			if (settings('market_member_barcode')) {
				event('market.on.user.login', [$user]);
			}
		}

		$token = $user->createToken($user->id)->accessToken;

		$this->bindOpenPlatform($user->id);

		return $this->success(['token_type' => 'Bearer', 'access_token' => $token]);
	}

	private function bindOpenPlatform($userId, $openID = '')
	{
		$openId = request('open_id') ? request('open_id') : $openID;
		$type   = 'miniprogram';
		$app_id = config('wechat.mini_program.shitang.app_id');

		if (empty($openId)) {
			return;
		}

		$userBind = UserBind::where('open_id', $openId)->where('type', 'miniprogram')->first();

		if ($userBind) {
			$this->userBindRepository->update(['user_id' => $userId], $userBind->id);
		} else {
			$this->userBindRepository->create(['open_id' => $openId, 'type' => $type, 'user_id' => $userId, 'app_id' => $app_id]);
		}
	}

	public function checkSessionKey()
	{
		$sessionID = request('entrySessionKey');

		if (\Cache::get($sessionID)) {
			return $this->success();
		} else {
			return $this->failed('entrySessionKey已失效');
		}
	}

	public function createSessionKey()
	{
		$code = request('code');
		if (empty($code)) {
			return $this->failed('缺失code');
		}

		$miniProgram = EasyWeChat::miniProgram('shitang');

		$result = $miniProgram->auth->session($code);

		if (!isset($result['session_key'])) {
			return $this->failed('获取session_key失败.');
		}

		$sessionKey = $this->create3rdSessionKey();
		\Cache::put($sessionKey, $result['session_key'], 7200);

		return $this->success(['entrySessionKey' => $sessionKey]);
	}

	public function create3rdSessionKey()
	{
		$session3rd = null;
		$strPol     = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
		$max        = strlen($strPol) - 1;
		for ($i = 0; $i < 16; $i++) {
			$session3rd .= $strPol[rand(0, $max)];
		}

		return $session3rd;
	}
}
