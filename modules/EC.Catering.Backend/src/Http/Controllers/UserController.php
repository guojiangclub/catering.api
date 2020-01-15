<?php

/*
 * This file is part of ibrand/catering-backend.
 *
 * (c) iBrand <https://www.ibrand.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GuoJiangClub\EC\Catering\Backend\Http\Controllers;

use Carbon\Carbon;
use GuoJiangClub\Catering\Component\Point\Model\Point;
use GuoJiangClub\Catering\Component\Point\Repository\PointRepository;
use GuoJiangClub\Catering\Component\User\Models\ElGroup;
use GuoJiangClub\Catering\Component\Balance\Model\Balance;
use GuoJiangClub\EC\Catering\Backend\Models\User;
use GuoJiangClub\EC\Catering\Backend\Models\UserGroup;
use ElementVip\Notifications\PointChanged;
use ElementVip\Notifications\PointRecord;
use ElementVip\Store\Backend\Facades\ExcelExportsService;
use GuoJiangClub\EC\Catering\Backend\Repositories\UserRepository;
use Encore\Admin\Facades\Admin as LaravelAdmin;
use Encore\Admin\Layout\Content;
use Excel;
use Illuminate\Http\Request;
use Response;
use Validator;

class UserController extends Controller
{
	protected $userRepository;
	protected $permissions;
	protected $integralRepository;
	protected $couponHistoryRepository;
	protected $orderLogRepository;
	protected $pointRepository;
	protected $cache;

	public function __construct(UserRepository $userRepository, PointRepository $pointRepository)
	{
		$this->userRepository  = $userRepository;
		$this->pointRepository = $pointRepository;
		$this->cache           = cache();
	}

	/**
	 * @param Request $request
	 *
	 * @return mixed
	 */
	public function index()
	{
		$users = $this->UsersSearch(['status' => 1]);

		return LaravelAdmin::content(function (Content $content) use ($users) {
			$content->header('会员管理');

			$content->breadcrumb(
				['text' => '会员管理', 'url' => 'member/users', 'no-pjax' => 1, 'left-menu-active' => '会员管理']
			);

			$content->body(view('catering-backend::auth.index', compact('users')));
		});
	}

	public function create()
	{
		$groups = UserGroup::all();

		return LaravelAdmin::content(function (Content $content) use ($groups) {
			$content->header('创建会员');

			$content->breadcrumb(
				['text' => '会员管理', 'url' => 'member/users', 'no-pjax' => 1],
				['text' => '创建会员', 'url' => 'member/users/create', 'no-pjax' => 1, 'left-menu-active' => '会员管理']
			);

			$content->body(view('catering-backend::auth.create', compact('groups')));
		});
	}

	/**
	 * @param Request $request
	 *
	 * @return mixed
	 */
	public function store(Request $request)
	{
		//验证
		$rules   = [
			'mobile'   => 'unique:' . config('ibrand.app.database.prefix', 'ibrand_') . 'user,mobile',
			'group_id' => 'required',
		];
		$message = [
			'required' => ':attribute 不能为空',
			'unique'   => ':attribute 已经存在',
			'email'    => ':attribute 格式不正确',
			'group_id' => ':attribute 不能为空',
		];

		$attributes = [
			'name'     => '会员名',
			'email'    => 'Email',
			'mobile'   => '手机号码',
			'group_id' => '用户等级分组',
		];

		$validator = Validator::make(
			$request->all(),
			$rules,
			$message,
			$attributes
		);
		if ($validator->fails()) {
			$warnings = $validator->messages();
			$warning  = $warnings->first();

			flash($warning, 'danger');

			return redirect()->back()->withInput();
		}

		$input = $request->except('assignees_roles', 'permission_user', '_token');

		if (isset($input['email']) && !empty($input['email'])) {
			$data['email'] = $input['email'];
		}

		if (isset($input['nick_name']) && !empty($input['nick_name'])) {
			$data['nick_name'] = $input['nick_name'];
		}

		if (isset($input['password']) && !empty($input['password'])) {
			$data['password'] = $input['password'];
		}

		$data['mobile']            = $input['mobile'];
		$data['status']            = isset($input['status']) ? 1 : 2;
		$data['confirmation_code'] = md5(uniqid(mt_rand(), true));
		$data['confirmed']         = isset($input['confirmed']) ? 1 : 0;
		$data['group_id']          = isset($input['group_id']) ? $input['group_id'] : 1;
		User::create($data);

		flash('用户创建成功', 'success');

		return redirect()->route('admin.users.index');
	}

	public function edit($id)
	{
		$user = $this->userRepository->findOrThrowException($id, false);
		if (isset($user->bind)) {
			$user->open_id = $user->bind->open_id;
		}

		$balance      = Balance::where('user_id', $user->id)->sum('value');
		$points       = $user->points()->paginate(20);
		$redirect_url = request('redirect_url');

		$groups = ElGroup::all();

		return LaravelAdmin::content(function (Content $content) use ($balance, $groups, $user, $points, $redirect_url) {
			$content->header('编辑会员');

			$content->breadcrumb(
				['text' => '会员管理', 'url' => 'member/users', 'no-pjax' => 1],
				['text' => '编辑会员', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '会员管理']
			);

			$content->body(view('catering-backend::auth.edit', compact('balance', 'groups', 'user', 'points', 'redirect_url'))
			);
		});
	}

	public function getUserPointData($uid, $type = 'offline')
	{
		if ('offline' == $type) {
			$where['type'] = $type;
		} else {
			$where['type'] = ['type', '<>', 'offline'];
		}
		$where['user_id'] = $uid;

		$pointData = $this->pointRepository->getPointsByConditions($where, 15);

		return $this->ajaxJson(true, $pointData);
	}

	/**
	 * @param         $id
	 * @param Request $request
	 *
	 * @return mixed
	 */
	public function update($id, Request $request)
	{
		//验证
		$rules   = [
			'email'  => "unique:" . config('ibrand.app.database.prefix', 'ibrand_') . "user,email,$id|email",
			'mobile' => "unique:" . config('ibrand.app.database.prefix', 'ibrand_') . "user,mobile,$id",
		];
		$message = [
			'required' => ':attribute 不能为空',
			'unique'   => ':attribute 已经存在',
			'email'    => ':attribute 格式不正确',
		];

		$attributes = [
//            "name" => "会员名",
'email'  => 'Email',
'mobile' => '手机号码',
		];

		$validator = Validator::make(
			$request->all(),
			$rules,
			$message,
			$attributes
		);
		if ($validator->fails()) {
			$warnings = $validator->messages();
			$warning  = $warnings->first();

			return $this->ajaxJson(false, [], 404, $warning);
		}

		$user               = User::find($id);
		$input              = $request->except('assignees_roles', 'permissions', 'userGroups');
		$input['email']     = trim($input['email']);
		$input['status']    = empty(request('status')) ? 2 : request('status');
		$input['confirmed'] = empty(request('confirmed')) ? 0 : request('confirmed');
		$input              = array_filter($input);

		if (!empty($input['email']) and $user->email !== $input['email']) {
			if (User::where('email', '=', $input['email'])->first()) {
				return $this->ajaxJson(false, [], 404, '系统已经存在此邮箱');
			}
		}

		$input['email'] = empty($input['email']) ? null : $input['email'];

		$this->userRepository->update($input, $id);

		if (request()->has('permissions')) {
			$selectRoles = request()->permissions;
		} else {
			$selectRoles = [];
		}
		$roles = Role::pluck('id')->toArray();
		if (!empty($roles)) {
			$user->detachRoles($roles);
		}
		if (!empty($selectRoles)) {
			$user->attachRoles($selectRoles);
		}

		$selectGroups = request('userGroups') ? request('userGroups') : [];
		$groups       = ElGroup::pluck('id')->toArray();
		if (!empty($groups)) {
			$user->detachGroups($groups);
		}
		if (count($selectGroups)) {
			$user->attachGroups($selectGroups);
		}

		return $this->ajaxJson(true, [], 200, '更新成功');
	}

	public function destroy($id)
	{
		$this->userRepository->destroy($id);
		flash('账号已删除', 'success');

		return redirect()->back();
	}

	public function mark($id, $status)
	{
		$this->userRepository->mark($id, $status);
		flash('用户更新成功', 'success');

		return redirect()->back();
	}

	public function deleted()
	{
		$users = $this->UsersSearch([], true);

		return LaravelAdmin::content(function (Content $content) use ($users) {
			$content->header('已删除会员');

			$content->breadcrumb(
				['text' => '会员管理', 'url' => 'member/users', 'no-pjax' => 1],
				['text' => '已删除会员', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '会员管理']
			);

			$content->body(view('catering-backend::auth.deleted', compact('users')));
		});
	}

	public function banned()
	{
		$users = $this->UsersSearch(['status' => 2]);

		return LaravelAdmin::content(function (Content $content) use ($users) {
			$content->header('已禁用会员');

			$content->breadcrumb(
				['text' => '会员管理', 'url' => 'member/users', 'no-pjax' => 1],
				['text' => '已禁用会员', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '会员管理']
			);

			$content->body(view('catering-backend::auth.banned', compact('users')));
		});
	}

	public function changePassword($id)
	{
		$user = $this->userRepository->findOrThrowException($id);

		return LaravelAdmin::content(function (Content $content) use ($user) {
			$content->header('更改密码');

			$content->breadcrumb(
				['text' => '会员管理', 'url' => 'member/users', 'no-pjax' => 1],
				['text' => '更改密码', 'url' => '', 'no-pjax' => 1, 'left-menu-active' => '会员管理']
			);

			$content->body(view('catering-backend::auth.change-password', compact('user')));
		});
	}

	public function updatePassword($id, Request $request)
	{
		$this->userRepository->updatePassword($id, $request->all());
		flash('密码修改成功!', 'success');

		return redirect()->route('admin.users.index');
	}

	public function resendConfirmationEmail($user_id)
	{
		$this->userRepository->resendConfirmationEmail($user_id);

		return redirect()->back()->withFlashSuccess(trans('激活邮件发送成功'));
	}

	public function userlist()
	{
		$users = $this->userRepository->searchUserPaginated([]);

		return LaravelAdmin::content(function (Content $content) use ($users) {
			$content->header('会员管理');

			$content->breadcrumb(
				['text' => '会员管理', 'url' => 'member/users', 'no-pjax' => 1, 'left-menu-active' => '会员管理']
			);

			$content->body(view('catering-backend::auth.userlist', compact('users')));
		});
	}

	public function integrallist($id)
	{
		if ($user = $this->userRepository->findOrThrowException($id, true)) {
			return view('catering-backend::auth.includes.user-integral-list')
				->withIntegral($this->integralRepository->getIntegralLogsPaginated(['user_id' => $user->id], 50));
		}
	}

	public function couponslist($id)
	{
		if ($user = $this->userRepository->findOrThrowException($id, true)) {
			return view('catering-backend::auth.includes.user-coupons-list')
				->withCoupons($this->couponHistoryRepository->getCouponsHistoryPaginated(['user_id' => $user->id], 50));
		}
	}

	public function orderslist($id)
	{
		if ($user = $this->userRepository->findOrThrowException($id, true)) {
			return view('catering-backend::auth.includes.user-orders-list')
				->withOrders($this->orderLogRepository->getUserOrderLogPaginated(['user_id' => $user->id], 50));
		}
	}

	public function UsersSearch($where = [], $delete = false)
	{
		if (!empty(request('name'))) {
			$where['name'] = ['like', '%' . request('name') . '%'];
		}

		if (!empty(request('email'))) {
			$where['email'] = ['like', '%' . request('email') . '%'];
		}

		if (!empty(request('mobile'))) {
			$where['mobile'] = ['like', '%' . request('mobile') . '%'];
		}

		if (true == $delete) {
			return $this->userRepository->getDeletedUsersPaginated($where);
		}

		return $this->userRepository->searchUserPaginated($where);
	}

	public function userexport()
	{
		$user_group = UserGroup::all();
		$type       = request('type');

		return view('catering-backend::auth.userexport', compact('user_group', 'type'));
	}

	public function getexport()
	{
		$input = request()->except('_token', 'stime', 'etime');
		$time  = [];
		$data  = [];

		foreach ($input as $k => $v) {
			if (empty($v)) {
				unset($input[$k]);
			}
		}

		if (!empty(request('etime')) && !empty(request('stime'))) {
			$input['created_at'] = ['<=', request('etime')];
			$time['created_at']  = ['>=', request('stime')];
		}

		if (!empty(request('etime'))) {
			$time['created_at'] = ['<=', request('etime')];
		}

		if (!empty(request('stime'))) {
			$time['created_at'] = ['>=', request('stime')];
		}

		$data   = $this->userRepository->getUserExportList($input, $time);
		$titles = ['会员名', '邮箱', '电话', '积分', '角色', '注册时间', '会员卡号', '申领日期', '注册姓名', '手机号', '出生年月日', 'open_id'];

		return ExcelExportsService::createExcelExport('User_', $data, $titles);
	}

	public function download()
	{
		$url = request('url');

		return Response::download(storage_path() . "/exports/$url");
	}

	public function addPoint()
	{
		$id   = request('user_id');
		$data = [
			'user_id' => $id,
			'action'  => 'admin_action',
			'note'    => request('note'),
			'value'   => request('value'),
			'status'  => 1];
		if (request('value') < 0) {
			$data['valid_time'] = 0;
		}
		$point = Point::create($data);

		event('point.change', $id);

		$user = User::find($id);
		$user->notify(new PointRecord(['point' => [
			'user_id'    => $id,
			'action'     => 'admin_action',
			'note'       => request('note'),
			'value'      => request('value'),
			'valid_time' => 0,
			'status'     => 1,]]));

		$user->notify((new PointChanged(compact('point')))->delay(Carbon::now()->addSecond(30)));

		return $this->ajaxJson();
	}

	public function importUser()
	{
		return view('catering-backend::auth.includes.import-user');
	}

	public function saveImport()
	{
		$data     = [];
		$filename = 'public' . request('upload_excel');
		Excel::load($filename, function ($reader) {
			$reader = $reader->getSheet(0);
			//获取表中的数据
			$results = $reader->toArray();

			foreach ($results as $key => $value) {
				if (0 != $key) {
					$data['nick_name']         = trim($value[0]);
					$data['mobile']            = trim($value[1]);
					$data['email']             = trim($value[2]);
					$data['name']              = trim($value[3]);
					$data['password']          = trim($value[4]);
					$data['status']            = 1;
					$data['confirmation_code'] = md5(uniqid(mt_rand(), true));
					$data['group_id']          = 1;

					if ($data['mobile'] AND User::where('mobile', $data['mobile'])->first()) {
						continue;
					}

					if ($data['email'] AND User::where('email', $data['email'])->first()) {
						continue;
					}

					if ($data['name'] AND User::where('name', $data['name'])->first()) {
						continue;
					}

					if (!$data['name'] AND !$data['mobile'] AND !$data['email']) {
						break;
					}

					$user = User::create($data);

					if ($user AND $value[5]) {
						$selectRoles = Role::where('display_name', trim($value[5]))->get()->pluck('id')->toArray();
						$user->attachRoles($selectRoles);
					}
				}
			}
		});

		return $this->ajaxJson(true, $data, 200, '');
	}

	/**
	 * 获取导出数据.
	 *
	 * @return mixed
	 */
	public function getExportData()
	{
		$page  = request('page') ? request('page') : 1;
		$limit = request('limit') ? request('limit') : 50;

		$where = [];
		$time  = [];
		if ($group_id = request('group_id')) {
			$where['group_id'] = $group_id;
		}

		if (!empty(request('etime')) && !empty(request('stime'))) {
			$where['created_at'] = ['<=', request('etime')];
			$time['created_at']  = ['>=', request('stime')];
		}

		if (!empty(request('etime'))) {
			$time['created_at'] = ['<=', request('etime')];
		}

		if (!empty(request('stime'))) {
			$time['created_at'] = ['>=', request('stime')];
		}

		$users = $this->userRepository->getExportUserData($where, $time, $limit);

		$lastPage = $users['lastPage'];
		$users    = $users['users'];
		$type     = request('type');

		$adminID   = auth('admin')->user()->id;
		$cacheName = request('cache') ? request('cache') : generate_export_cache_name('export_users_cache' . $adminID . '_');

		if ($this->cache->has($cacheName)) {
			$cacheData = $this->cache->get($cacheName);
			$this->cache->put($cacheName, array_merge($cacheData, $users), 300);
		} else {
			$this->cache->put($cacheName, $users, 300);
		}

		if ($page == $lastPage) {
			$title = ['会员名', '邮箱', '电话', '积分', '角色', '注册时间', '会员卡号', '申领日期', '注册姓名', '手机号', '出生年月日', 'open_id'];

			return $this->ajaxJson(true, ['status' => 'done', 'url' => '', 'type' => $type, 'title' => $title, 'cache' => $cacheName, 'prefix' => 'users_data_']);
		}
		$url_bit = route('admin.users.getExportData', array_merge(['page' => $page + 1, 'limit' => $limit, 'cache' => $cacheName], request()->except('page', 'limit', 'cache')));

		return $this->ajaxJson(true, ['status' => 'goon', 'url' => $url_bit, 'page' => $page, 'totalPage' => $lastPage]);
	}
}
