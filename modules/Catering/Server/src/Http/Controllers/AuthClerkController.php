<?php

namespace GuoJiangClub\Catering\Server\Http\Controllers;

use GuoJiangClub\Catering\Backend\Models\Clerk;
use Illuminate\Http\Request;
use iBrand\Sms\Facade as Sms;
use Validator;
use Hash;

class AuthClerkController extends Controller
{
	public function login(Request $request)
	{
		$input      = $request->except('_token', 'file');
		$rules      = [
			'mobile'   => 'required',
			'password' => 'required',

		];
		$message    = [
			'required' => ':attribute 不能为空',
		];
		$attributes = [
			'mobile'   => '手机号',
			'password' => '密码',
		];

		$validator = Validator::make($input, $rules, $message, $attributes);
		if ($validator->fails()) {
			$warnings     = $validator->messages();
			$show_warning = $warnings->first();

			return $this->failed($show_warning);
		}

		$user = Clerk::where('mobile', $input['mobile'])->where('status', 1)->first();
		if (!$user) {
			return $this->failed('账号不存在');
		}

		if (empty($user->password) || !Hash::check($input['password'], $user->password)) {
			return $this->failed('手机号或密码错误');
		}

		$type = 'clerk';
		if ($user->is_clerk_owner) {
			$type = 'clerk_leader';
		}

		$token = $user->createToken($user->mobile)->accessToken;

		return $this->success(['token_type' => 'Bearer', 'access_token' => $token, 'type' => $type]);
	}

	public function resetPassword(Request $request)
	{
		$input      = $request->except('_token', 'file');
		$rules      = [
			'mobile'      => 'required',
			'verify_code' => 'required',
			'password'    => 'required',

		];
		$message    = [
			'required' => '请输入 :attribute ',
		];
		$attributes = [
			'mobile'      => '手机号',
			'verify_code' => '验证码',
			'password'    => '密码',
		];

		$validator = Validator::make($input, $rules, $message, $attributes);
		if ($validator->fails()) {
			$warnings     = $validator->messages();
			$show_warning = $warnings->first();

			return $this->failed($show_warning);
		}

		if (!Sms::checkCode($input['mobile'], $input['verify_code'])) {
			return $this->failed('验证码错误');
		}

		$user = Clerk::where('mobile', $input['mobile'])->where('status', 1)->first();
		if (!$user) {
			return $this->failed('账号不存在');
		}

		$user->password = bcrypt($input['password']);
		$user->save();

		return $this->success('修改成功');
	}
}