<?php

namespace GuoJiangClub\Catering\Backend\Http\Controllers;

use GuoJiangClub\Catering\Backend\Models\Clerk;
use GuoJiangClub\Catering\Backend\Models\ClerkBind;
use GuoJiangClub\Catering\Backend\Repositories\ClerkRepository;
use Maatwebsite\Excel\Facades\Excel;
use iBrand\Backend\Http\Controllers\Controller;
use Predis\Client;
use Validator;
use DB;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;

class ClerkController extends Controller
{

    protected     $shopRepository;
    protected     $clerkRepository;
    public static $client = null;

    public function __construct(ClerkRepository $clerkRepository)
    {
        $this->clerkRepository = $clerkRepository;
    }

    public function index()
    {
        $where           = [];
        $status          = request('status') === 0 ? 0 : 1;
        $where['status'] = $status;

        $title = request('title');
        if (request('value') != '') {
            $where["$title"] = ['like', '%' . request('value') . '%'];
        }

        $lists     = $this->clerkRepository->getClerkList($where);
        $inUse     = Clerk::where('status', 1)->count();
        $forbidden = Clerk::where('status', 0)->count();

        return Admin::content(function (Content $content) use ($lists, $forbidden, $inUse, $status) {
            $content->header('店员列表');

            $content->breadcrumb(
                ['text' => '店员列表', 'no-pjax' => 1, 'left-menu-active' => '店员管理']
            );

            $content->body(view('backend-shitang::clerk.index', compact('lists', 'inUse', 'forbidden', 'status')));
        });
    }

    public function toggleStatus()
    {

        $status = request('status');
        $id     = request('aid');
        $user   = Clerk::find($id);
        if ($user) {
            $user->status = $status;
            $user->save();

            return $this->ajaxJson(true, 200, '', []);
        }

        return $this->ajaxJson(false, 400, '操作失败', []);
    }

    public function create()
    {
        $scan        = DB::table('we_qr_codes')->where('key', 'clerk_bind')->first();
        $qr_code_url = '';
        if ($scan) {
            $qr_code_url = $scan->qr_code_url;
        }

        return Admin::content(function (Content $content) use ($qr_code_url) {

            $content->header('添加店员');

            $content->breadcrumb(
                ['text' => '添加店员', 'no-pjax' => 1, 'left-menu-active' => '店员管理']
            );

            $content->body(view('backend-shitang::clerk.create', compact('qr_code_url')));
        });
    }

    public function edit($clerk_id)
    {
        $clerk     = $this->clerkRepository->find($clerk_id);
        $clerkBind = null;
        if ($clerk->openid) {
            $clerkBind = ClerkBind::where('openid', $clerk->openid)->first();
        }

        $scan        = DB::table('we_qr_codes')->where('key', 'clerk_bind')->first();
        $qr_code_url = '';
        if ($scan) {
            $qr_code_url = $scan->qr_code_url;
        }

        return Admin::content(function (Content $content) use ($clerk, $clerkBind, $qr_code_url) {

            $content->header('编辑店员信息');

            $content->breadcrumb(
                ['text' => '编辑店员信息', 'no-pjax' => 1, 'left-menu-active' => '店员管理']
            );

            $content->body(view('backend-shitang::clerk.edit', compact('clerk', 'clerkBind', 'qr_code_url')));
        });
    }

    public function update($clerk_id)
    {
        $input   = request()->except(['_token', 'file']);
        $rules   = [
            'name'                  => "required",
            'password'              => 'required|confirmed',
            'password_confirmation' => 'required',
            'email'                 => "unique:" . config('ibrand.app.database.prefix', 'ibrand_') . "clerk,email,$clerk_id|email",
            'clerk_no'              => "unique:" . config('ibrand.app.database.prefix', 'ibrand_') . "clerk,clerk_no,$clerk_id",
            'mobile'                => "required|unique:" . config('ibrand.app.database.prefix', 'ibrand_') . "clerk,mobile,$clerk_id",
        ];
        $message = [
            "required"  => ":attribute 不能为空",
            "confirmed" => "两次输入的密码不一致",
            "unique"    => ":attribute 已经存在",
            "email"     => ":attribute 格式不正确",
            "integer"   => ":attribute 必须为整数",
            "min"       => "无效的 :attribute",
        ];

        $attributes = [
            "name"                  => "名称",
            "password"              => "密码",
            "password_confirmation" => "确认密码",
            "email"                 => "Email",
            "mobile"                => "手机号码",
            "clerk_no"              => '工号',
        ];

        $validator = Validator::make($input, $rules, $message, $attributes);
        if ($validator->fails()) {
            $warnings = $validator->messages();
            $warning  = $warnings->first();

            return $this->ajaxJson(false, 400, '', $warning);
        }

        unset($input['password_confirmation']);
        $clerk = $this->clerkRepository->find($clerk_id);
        if ($input['password'] != $clerk->password) {
            if (!preg_match('/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9a-zA-Z]+$/', $input['password']) || strlen($input['password']) < 6) {
                return $this->ajaxJson(false, 400, '', '密码格式错误,必须包含数字，字母；且不小于6位。');
            }

            $input['password'] = bcrypt($input['password']);
        }

        if (1 == $input['is_clerk_owner']) {
            $clerk = Clerk::where('status', 1)->where('is_clerk_owner', 1)->where('id', '!=', $clerk->id)->first();
            if ($clerk) {
                return $this->ajaxJson(false, [], 500, '店铺已有店长');
            }
        }

        if (1 == $input['receive_template_message'] && !$input['openid']) {
            return $this->ajaxJson(false, [], 500, '接收统计模板消息 必须绑定微信号');
        }

        try {

            DB::beginTransaction();

            $this->clerkRepository->update($input, $clerk_id);

            DB::commit();

            return $this->ajaxJson(true);
        } catch (\Exception $exception) {

            DB::rollBack();

            \Log::info($exception->getMessage() . $exception->getTraceAsString());

            return $this->ajaxJson(false, [], 500, $exception->getMessage());
        }
    }

    public function store()
    {
        $input   = request()->except(['_token', 'file']);
        $rules   = [
            'name'                  => 'required',
            'password'              => 'required|confirmed',
            'password_confirmation' => 'required',
            'email'                 => "unique:" . config('ibrand.app.database.prefix', 'ibrand_') . "clerk,email|email",
            'clerk_no'              => "required|unique:" . config('ibrand.app.database.prefix', 'ibrand_') . "clerk,clerk_no",
            'mobile'                => "required|unique:" . config('ibrand.app.database.prefix', 'ibrand_') . "clerk,mobile",

        ];
        $message = [
            "required"  => ":attribute 不能为空",
            "confirmed" => "两次输入的密码不一致",
            "unique"    => ":attribute 已经存在",
            "email"     => ":attribute 格式不正确",
            "integer"   => ":attribute 必须为整数",
            "min"       => "无效的 :attribute",
        ];

        $attributes = [
            "name"                  => "名称",
            "password"              => "密码",
            "password_confirmation" => "确认密码",
            "email"                 => "Email",
            "mobile"                => "手机号码",
            "clerk_no"              => '工号',
        ];

        $validator = Validator::make($input, $rules, $message, $attributes);
        if ($validator->fails()) {
            $warnings = $validator->messages();
            $warning  = $warnings->first();

            return $this->ajaxJson(false, 400, '', $warning);
        }

        if (!preg_match('/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9a-zA-Z]+$/', $input['password']) || strlen($input['password']) < 6) {
            return $this->ajaxJson(false, 400, '', '密码格式错误,必须包含数字，字母；且不小于6位');
        }

        if (1 == $input['is_clerk_owner']) {
            $clerk = Clerk::where('status', 1)->where('is_clerk_owner', 1)->first();
            if ($clerk) {
                return $this->ajaxJson(false, [], 500, '店铺已有店长');
            }
        }

        if (1 == $input['receive_template_message'] && !$input['openid']) {
            return $this->ajaxJson(false, [], 500, '接收统计模板消息 必须绑定微信号');
        }

        $input['password'] = bcrypt($input['password']);
        unset($input['password_confirmation']);
        foreach ($input as $k => $item) {
            if ($item == '') {
                unset($input[$k]);
            }
        }

        try {
            DB::beginTransaction();

            $clerk = $this->clerkRepository->create($input);

            DB::commit();

            if (1 == $clerk->receive_template_message && $clerk->openid) {
                event('st.send.statistics.message');
            }

            return $this->ajaxJson(true);
        } catch (\Exception $exception) {
            DB::rollBack();
            \Log::info($exception->getMessage() . $exception->getTraceAsString());

            return $this->ajaxJson(false, [], 500, $exception->getMessage());
        }
    }

    public function Import()
    {
        return view('shop-backend::clerk.import');
    }

    public function saveImport()
    {
        $data       = [];
        $filename   = 'public' . request('upload_excel');
        $error_list = [];
        Excel::load($filename, function ($reader) {
            $reader = $reader->getSheet(0);
            //获取表中的数据
            $results  = $reader->toArray();
            $abnormal = [];
            $input    = [];
            $i        = 0;
            foreach ($results as $key => $value) {
                if ($key != 0) {
                    if (!empty($value[0]) || !empty($value[1]) || !empty($value[2]) || !empty($value[3]) || !empty($value[4]) || !empty($value[5])) {
                        $input['sku']      = $value[0];
                        $input['bar_code'] = $value[1];
                        try {
                            $res = $this->barCodeRepository->create($input);
                            $i++;
                        } catch (\Exception $e) {
                            $abnormal[] = $key + 1;
                        }
                    }
                }
            }

            self::$num      = $i;
            self::$abnormal = $abnormal;
        });

        if (count(self::$abnormal)) {
            $abnormal         = array_unique(self::$abnormal);
            $data['abnormal'] = '以下列号数据未导入:' . implode(' ', $abnormal);
        } else {
            $data['abnormal'] = '';
        }

        $data['num'] = empty(self::$num) ? '0' : self::$num;

        return $this->ajaxJson(true, $data, 200, '');
    }

    public static function init()
    {
        if (!self::$client || !self::$client instanceof Client) {
            $options      = [
                'host'     => env('REDIS_HOST'),
                'port'     => env('REDIS_PORT'),
                'password' => env('REDIS_PASSWORD'),
            ];
            self::$client = new Client($options);
        }

        return self::$client;
    }

    public function bindWeChat()
    {
        $client = self::init();
        while (true) {
            $userInfo = $client->get('userInfo');
            if ($userInfo) {
                $client->del(['userInfo']);

                return $this->ajaxJson(true, json_decode($userInfo, true));
            } else {
                return $this->ajaxJson(false, [], 200, '等待用户代码');
            }

            sleep(1);
        }
    }

    public function delete()
    {
        $clerk_id = request('id');

        Clerk::where('id', $clerk_id)->delete();

        return $this->ajaxJson();
    }
}
