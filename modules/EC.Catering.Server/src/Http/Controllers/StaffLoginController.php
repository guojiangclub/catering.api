<?php

namespace ElementVip\Server\Http\Controllers;

use Carbon\Carbon;
use ElementVip\Component\User\Models\Role;
use ElementVip\Component\User\Repository\UserRepository;
use ElementVip\TNF\Core\Models\Staff;
use Validator;
use RuntimeException;
use Illuminate\Auth\Events\Login;
use iBrand\Sms\Facade as Sms;

class StaffLoginController extends Controller
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function login()
    {
        $mobile = request('mobile');
        $code = request('code');

        if (is_null($model = config('auth.providers.users.model'))) {
            throw new RuntimeException('Unable to determine user model from configuration.');
        }

        $credentials = [
            'mobile' => $mobile,
            'verifyCode' => $code,
        ];

        //验证数据
        $validator = Validator::make($credentials, [
            'mobile' => 'required|confirm_mobile_not_change|confirm_rule:mobile_required',
            'verifyCode' => 'required|verify_code',
        ]);

        /*if ($validator->fails()) {
            return $this->api([], false, 200, '短信验证失败');
        }*/

        if (!Sms::checkCode(\request('mobile'), \request('code'))) {
            return $this->api([], false, 200, '短信验证失败');
        }

        if (!$staff = Staff::where(['mobile'=>$mobile])->first()) {
            return $this->api([], false, 200, '不存在此员工');
        }

        if ($staff->active_status == 0) {
            return $this->api([], false, 200, '不存在此员工');
        }

        if (!$user = $model::where('mobile', $mobile)->first()) {
            $user = $model::create([
                'mobile' => $mobile
                , 'card_limit' => date('Y-m-d', time())
                , 'group_id' => 1
            ]);
        }

        if ($user->status == $model::STATUS_FORBIDDEN) {
            return $this->api($user, false, 403, '您的账号已被禁用，联系网站管理员或客服！');
        }

        $token = $user->createToken($mobile)->accessToken;

        $employee = false;
        if(!empty($staff->activate_at)){
            $employee = true;
        }

        event(new Login($user, true));

        return $this->api([
            'token_type' => 'Bearer',
            'access_token' => $token,
            'employee' => $employee
        ]);
    }

    public function validateStaff()
    {

        $email = request('email');
        $staff_id = request('staff_id');
        $year = request('year');
        $month = request('month');

        if (is_null($model = config('auth.providers.users.model'))) {
            throw new RuntimeException('Unable to determine user model from configuration.');
        }

        $user = request()->user();
        $mobile = $user->mobile;

        $timestamp = mktime(0, 0, 0, $month, 1, $year);
        $hiredate_at = date('Y-m', $timestamp);

        if (!$staff = Staff::where(['mobile' => $mobile, 'email' => $email, 'staff_id' => $staff_id])->first()) {
            return $this->api([], false, 200, '对不起，您的工号或邮箱有误，激活失败');
        }

        if ($staff->active_status == 0) {
            return $this->api([], false, 200, '不存在此员工');
        }

        if(date('Y-m', strtotime($staff->hiredate_at)) !== $hiredate_at){
            return $this->api([], false, 200, '对不起，您的入职日期有误，激活失败');
        }

        if ($role = Role::where('name', 'employee')->first()) {
            if (!$user->hasRole($role->name)) {
                $user->attachRole($role);
            }
            $staff->activate_at = Carbon::now();
            $staff->save();
        }

        return $this->api([], true, 200, '激活成功');

    }

}
