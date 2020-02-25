<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016-08-23
 * Time: 12:02
 */

namespace GuoJiangClub\EC\Catering\Server\Http\Controllers;


use Carbon\Carbon;
use ElementVip\Component\Address\Models\Address;
use ElementVip\Component\Balance\Model\Balance;
use ElementVip\Component\Balance\Repository\BalanceRepository;
use ElementVip\Component\Discount\Repositories\CouponRepository;
use ElementVip\Component\Favorite\Models\Favorite;
use GuoJiangClub\Catering\Component\Order\Models\Order;
use ElementVip\Component\Order\Repositories\OrderRepository;
use ElementVip\Component\Point\Repository\PointRepository;
use ElementVip\Component\Refund\Models\Refund;
use ElementVip\Component\User\Models\User;
use ElementVip\Distribution\Core\Models\Agent;
use ElementVip\Server\Exception\UserExistsException;
use ElementVip\Server\Transformers\BankAccountTransformer;
use ElementVip\Server\Transformers\BalanceTransformer;
use ElementVip\Server\Transformers\GroupTransformer;
use ElementVip\Server\Transformers\PointTransformer;
use ElementVip\Server\Transformers\UserTransformer;
use ElementVip\Wechat\Server\Wx\WXBizDataCrypt;
use iBrand\Wanyou\Core\Models\Shops;
use Illuminate\Http\Request;
use ElementVip\Component\User\Repository\UserRepository;
use ElementVip\Component\Card\Repository\CardRepository;
use Validator;
use Image;
use ElementVip\Component\BankAccount\Model\BankInfo;
use ElementVip\Component\BankAccount\Repository\BankAccountRepository;
use iBrand\Sms\Facade as Sms;
use EasyWeChat;
use ElementVip\Activity\Core\Models\Like;
use iBrand\Wanyou\Server\Repositories\CouponRepository as MarketCouponRepository;

class UserController extends Controller
{

	private $user;

	private $point;
	private $balance;
	private $bankAccount;
	private $card;
	private $order;
	protected $couponRepository;
	protected $marketCouponRepository;

	public function __construct(UserRepository $userRepository
		/*, CardRepository $cardRepository*/
		, PointRepository $pointRepository
		, BalanceRepository $balance
		, BankAccountRepository $bankAccount
		, OrderRepository $orderRepository
		, CouponRepository $couponRepository, MarketCouponRepository $marketCouponRepository)
	{
		$this->user = $userRepository;
		$this->point = $pointRepository;
		$this->balance = $balance;
		$this->bankAccount = $bankAccount;
		$this->order = $orderRepository;
		$this->couponRepository = $couponRepository;
		$this->marketCouponRepository = $marketCouponRepository;

		/*$this->card = $cardRepository;*/
	}

	public function me()
	{
		// return $this->response()->item(request()->user()->with('group')->first(), new UserTransformer());
		$user = request()->user();
		if ($user->card()->first()) {
			$user->active_card = true;
		} else {
			$user->active_card = false;
		}
		$user->employee = false;
		$user->coach = false;
		//foreach ($user->roles as $role) {
			/*if ($role->name == 'employee' AND
                $staff = Staff::where('mobile', $user->mobile)->first() AND
                $staff->active_status == 1
            )
                $user->employee = true;*/
			/*if ($role->name == 'coach')
				$user->coach = true;*/
		//}

		$sumPointToday = $this->point->getDailySumByAction($user->id, 'daily_login');
		$user->is_sign = true;
		if ($sumPointToday == 0) {
			$user->is_sign = false;
		}
		return $this->response()->item($user, new UserTransformer());
	}

	public function getGroup()
	{
		return $this->response()->item(request()->user()->group, new GroupTransformer());
	}

	public function index()
	{

	}


	/**
	 *  balance fluctuation
	 */

	public function balanceFluctuation()
	{
		return $this->response()->paginator($this->balance->fluctuation(request()->user()->id)->paginate(),
			new BalanceTransformer());
	}

	/**
	 *  sum of balance
	 *  因为提现只有佣金，移动到Agent Server
	 */

	public function balanceSum()
	{
		$limit = settings('distribution_limit') ? settings('distribution_limit') : 10;

		return $this->api(['sumBalance' => $this->balance->getSum(request()->user()->id), 'limit' => $limit]);
		/*return response()->json(
            [
                'success' => 'true',
                'sumBalance' => $this->balance->getSum(request()->user()->id),
            ]
        );*/
	}

	/**
	 *  the function will record the user recharge
	 *
	 */
	public function balanceRecharge()
	{
		$validator = Validator::make(request()->all(), [
			'value' => 'required|Numeric',
		]);
		if ($validator->fails()) {
			return $this->api('', false, 400, $validator->errors());
		};

		$arr = $this->balanceValidator(0);

		$this->balance->addRecord(array_merge(['user_id' => request()->user()->id], $arr));

		return $this->api($this->balanceSum());

	}

	/**
	 *  the function will record the user consume
	 *
	 */
	public function balanceConsume()
	{
		$validator = Validator::make(request()->all(), [
			'value' => 'required|Numeric',
		]);
		if ($validator->fails()) {
			return $this->api('', false, 400, $validator->errors());
		};
		$arr = $this->balanceValidator(1);

		$this->balance->addRecord(array_merge(['user_id' => request()->user()->id], $arr));

		return $this->api($this->balanceSum());
	}

	private function balanceValidator($flag)
	{
		$user = request()->user();
		if (1 == $flag) {
			$value = request()->value > 0 ? -request()->value : request()->value;
			$note = empty(request()->note) ? 'consume' : request()->note;
		} else {
			$value = request()->value < 0 ? -request()->value : request()->value;
			$note = empty(request()->note) ? 'recharge' : request()->note;
		}

		$type = empty(request()->type) ? 0 : request()->type;

		$arr = ['user_id' => $user->id, 'value' => $value, 'type' => $type, 'note' => $note];

		return $this->api($arr);
	}


	/**
	 *  user's BankAccount
	 *
	 * */
	public function addBankAccount()
	{
		$user = request()->user();

		$validator = Validator::make(request()->all(), [
			'bank_card_number' => 'required',
			'bank_id' => 'required',
			'owner_name' => 'required',
		]);

		if ($validator->fails()) {
			return $this->api('', false, 400, $validator->errors());
		};

		$bank_card_number = request()->bank_card_number;
		switch ($bank_card_number) {
			case isMobile($bank_card_number):
				break;
			case isMail($bank_card_number):
				break;
			case isNumber($bank_card_number):
				break;
			default:
				return $this->api('', false, 400, '账号格式有误');

		}

		if (BankInfo::find(request()->bank_id)->bank_name != '支付宝') {
			$validator = Validator::make(request()->all(), [
				'sub_branch' => 'required',
			]);

			if ($validator->fails()) {
				return $this->api('', false, 400, $validator->errors());
			};
		}
		if ($this->bankAccount->getBankAccountByCardNumber($bank_card_number)) {
			return $this->api('', false, 400, '此账号已存在');
		}

		$this->bankAccount->create(array_merge(['user_id' => $user->id], request()->only(['bank_card_number', 'bank_id', 'owner_name', 'sub_branch'])));

		return $this->api();
	}

	public function updateBankAccount($id)
	{
		$user = request()->user();

		$validator = Validator::make(request()->all(), [
			'bank_card_number' => 'required',
			'bank_id' => 'required',
			'owner_name' => 'required',
		]);

		if ($validator->fails()) {
			return $this->api('', false, 400, $validator->errors());
		};

		$bank_card_number = request()->bank_card_number;
		switch ($bank_card_number) {
			case isMobile($bank_card_number):
				break;
			case isMail($bank_card_number):
				break;
			case isNumber($bank_card_number):
				break;
			default:
				return $this->api('', false, 400, '账号格式有误');

		}

		if (BankInfo::find(request()->bank_id)->bank_name != '支付宝') {
			$validator = Validator::make(request()->all(), [
				'sub_branch' => 'required',
			]);

			if ($validator->fails()) {
				return $this->api('', false, 400, $validator->errors());
			};
		}
		if ($this->bankAccount->getBankAccountByCardNumber($bank_card_number)) {
			return $this->api('', false, 400, '此账号已存在');
		}
		$this->bankAccount->update(request()->only(['bank_card_number', 'bank_id', 'owner_name', 'sub_branch']), $id);

		return $this->api();
	}

	public function deleteBankAccount($id)
	{
		$user = request()->user();

		$status = $this->bankAccount->delete($id);

		if ($status) {
			return $this->api();
		} else {
			return $this->api('', false, 400, 'deleted failed');
		}

	}

	/**
	 * 批量删除银行账号
	 * @return \Dingo\Api\Http\Response
	 */
	public function deleteBankAccountByIds()
	{
		$user = request()->user();
		$ids = request('ids');

		$this->bankAccount->deleteAccountByIds($user->id, $ids);
		return $this->api();
	}


	public function showBankAccountList()
	{

		$data = $this->bankAccount->getBankAccountsByUser(request()->user()->id);
		$type = settings('distribution_commission_wechat') == 1 ? 'customer_wechat' : 'customer_account';

		if ($data) {
			return $this->response()->collection($data,
				new BankAccountTransformer())->setMeta(['type' => $type]);
		} else {
			return $this->api('', true, 200, '无数据');
		}
	}


	public function showBankList()
	{
		return $this->response()->collection(BankInfo::all(), new BankAccountTransformer());
	}


	public function showBankItem($id)
	{
		$bankAccount = $this->bankAccount->with('bank')->find($id);
//        $collection->filter(function($item)use($id){
//           $item->bank_card_number = ;
//        });
//        return $this->response()->collection($collection, new BankAccountTransformer());
		return $this->api($bankAccount);
	}

	public function amountBankAccount()
	{
		$hasAccount = false;
		$accountCount = $this->bankAccount->getBankAccountsByUser(request()->user()->id)->count();
		if ($accountCount > 0) {
			$hasAccount = true;
		}
		$type = settings('distribution_commission_wechat') ? 'customer_wechat' : 'customer_account';

		return $this->api(['type' => $type, 'hasAccount' => $hasAccount, 'accountCount' => $accountCount]);

		/*return $this->api($this->bankAccount->getBankAccountsByUser(request()->user()->id)->count());*/
	}


	/**
	 *  user's point
	 */
	public function pointList()
	{
		$type = request('type') ? request('type') : 'default';
		$list = request()->user()->points()->type($type);
		if (request('balance') == 'in')
			$list = $list->where('value', '>', 0);
		if (request('balance') == 'out')
			$list = $list->where('value', '<', 0);
		$list = $list->orderBy('created_at', 'desc')->paginate();
		return $this->response()->paginator($list, new PointTransformer());
	}


	public function show($id)
	{
		$user = $this->user->with('group')->with('size')->find($id);
		return $this->response()->item($user, new UserTransformer());
	}

	public function updateInfo()
	{
		$user = request()->user();
		$data = array_filter(request()->only(['name', 'nick_name', 'sex', 'birthday', 'city', 'education', 'qq', 'avatar', 'email', 'password']));
		$size_input = request('size') ?: [];
		$size_input = array_filter(array_only($size_input, ['upper', 'lower', 'shoes']));

		if (isset($data['name']) and ($data['name']) != $user->name AND User::where('name', $data['name'])->first()) {
			return response()->json([
				'status' => false,
				'message' => '此用户名已存在',
			]);
		}

		if (isset($data['email']) and ($data['email']) != $user->email AND User::where('email', $data['email'])->first()) {
			return response()->json([
				'status' => false,
				'message' => '该邮箱已被使用',
			]);
		}

		$user->fill($data);
		$user->save();
		$size = $user->size;
		if ($size) {
			$size->update($size_input);
			$size->save();
		} else {
			$user->size()->create($size_input);
		}


		event('complete_info', [$user, 'complete_info']);

		// return $this->response()->item($user, new UserTransformer());
		return response()->json([
			'status' => true,
			'message' => "修改成功"
		]);

	}

	public function uploadAvatar(Request $request)
	{
		//TODO: 需要验证是否传入avatar_file 参数
		$file = $request->file('avatar_file');
		$Orientation = $request->input('Orientation');

		$destinationPath = storage_path('app/public/uploads');
		if (!is_dir($destinationPath)) {
			mkdir($destinationPath, 0777, true);
		}

		$extension = $file->getClientOriginalExtension();
		$filename = $this->generaterandomstring() . '.' . $extension;

		$image = $file->move($destinationPath, $filename);

		$img = Image::make($image);

		switch ($Orientation) {
			case 6://需要顺时针（向左）90度旋转
				$img->rotate(-90);
				break;
			case 8://需要逆时针（向右）90度旋转
				$img->rotate(90);
				break;
			case 3://需要180度旋转
				$img->rotate(180);
				break;
		}

		if (request('action_type') == 'full') {
			$img->resize(320, null, function ($constraint) {
				$constraint->aspectRatio();
			});
		} else {
			$img->resize(320, null, function ($constraint) {
				$constraint->aspectRatio();
			})->crop(320, 320, 0, 0)->save();
		}


		if (request('action') == 'save') {
			$user = $request->user();
			$user->avatar = '/storage/uploads/' . $filename;
			$user->save();
		}

		return $this->api(['url' => url('/storage/uploads/' . $filename)]);
	}

	/**
	 * @param Request $request
	 * @return \Dingo\Api\Http\Response|void
	 */
	public function updatePassword(Request $request)
	{
		$user = $request->user();

		$check = \Hash::check($request->input('old_password'), $user->password);
		if (!$check) {
			return $this->api([], false, 200, '原始密码错误');
		}

		$validator = Validator::make($request->all(), [
			'password' => 'required|min:6|confirmed|max:25',
		]);

		if ($validator->fails()) {
			return $this->api([], false, 200, '密码长度少于6位或确认密码不一致');
		}

		if (!preg_match('/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9a-zA-Z]+$/', $request->input('password'))) {
			return $this->api([], false, 200, '密码只能是数字和字母组合');
		}

		$user = $this->user->update(['password' => $request->input('password')], $user->id);


		return $this->response()->item($user, new UserTransformer());
	}

	public function updateMobile(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'mobile' => 'required|confirm_mobile_not_change|confirm_rule:mobile_required',
			'code' => 'required|verify_code',
		]);


		/*if ($validator->fails()) {
            return $this->api(null, false, 400,'验证码错误');
        };*/

		if (!Sms::checkCode(\request('mobile'), \request('code'))) {
			return $this->api(null, false, 400, '验证码错误');
		}


		$user = $request->user();

		if ($existUser = $this->user->findWhere(['mobile' => request('mobile')])->first()) {
			return $this->api(null, false, 400, '此手机号已绑定账号');
		}
		// $card = $user->card;
		// $card = $this->card->update(['mobile' => $request->input('mobile')], $card->id);
		$user = $this->user->update(['mobile' => $request->input('mobile')], $user->id);

		event('user.update.mobile', [$user]);
		event('verify_mobile', $user);

		return $this->response()->item($user, new UserTransformer());
	}

	public function updateEmail(Request $request)
	{
		$user = $request->user();

		$validator = Validator::make($request->all(), [
			'email' => 'required| email',
		]);
		if ($validator->fails()) {
			return response()->json([
				'success' => 'false',
				'message' => $validator->errors(),
			]);
		};

		$res = $this->user->update(['email' => $request->input('email')], $user->id);

		return $this->response()->item($res, new UserTransformer());
	}


	public function getUser(Request $request)
	{
		$credentials = $request->except('access_token', 'scope');

		$user = $this->user->getUserByCredentials($credentials);

		return $this->response()->item($user, new UserTransformer());
	}

	public function updateUser($id, Request $request)
	{
		$user = $this->user->update($request->except('_token'), $id);

		return $this->response()->item($user, new UserTransformer());
	}

	public function register(Request $request)
	{
		if ($user = $this->user->getUserByCredentials($request->only('email', 'name', 'mobile'))) {
			throw new UserExistsException();
		}

		$user = $this->user->create($request->except('access_token', 'scope'));

		return $this->response()->item($user, new UserTransformer());
	}

	private function generaterandomstring($length = 10)
	{
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}

	public function ucenter()
	{
		$user = request()->user();
		$newCount = $this->order->getOrderCountByUserAndStatus(request()->user()->id, Order::STATUS_NEW);
		$paidCount = $this->order->getOrderCountByUserAndStatus(request()->user()->id, Order::STATUS_PAY);
		$deliveredCount = $this->order->getOrderCountByUserAndStatus(request()->user()->id, Order::STATUS_DELIVERED);
		$receiveCount = $this->order->getOrderCountByUserAndStatus(request()->user()->id, Order::STATUS_RECEIVED);
		$favCount = Favorite::where('user_id', $user->id)->count();
		$addressCount = Address::where('user_id', $user->id)->count();
		$sum = Balance::sumByUser($user->id);
		if (!is_numeric($sum)) {
			$sum = 0;
		} else {
			$sum = (int)$sum / 100;
		}
		$balance = $sum;
		$point = $this->point->getSumPointValid($user->id);
		$coupon = $this->couponRepository->getValidCouponCountByUser($user->id);


		return $this->api(compact('newCount', 'paidCount', 'deliveredCount', 'receiveCount', 'favCount', 'addressCount', 'balance', 'point', 'coupon'));
	}

	public function marketUserCenter()
	{
		$user = request()->user();
		$type = request('type');
		if ($type == 'activity') {
			$favorite = Like::where('favoriteable_type', 'activity')->where('user_id', $user->id)->count();
		} elseif ($type == 'shop') {
			$favorite = Favorite::where('favoriteable_type', Shops::class)->where('user_id', $user->id)->count();
		}

		if($type=='shop'){
			$coupon = $this->marketCouponRepository->getValidCouponCountByUser($user->id);
		} else {
			$coupon = $this->couponRepository->getValidCouponCountByUser($user->id);
		}

		$point = $this->point->getSumPointValid($user->id);

		return $this->api(compact('coupon', 'point', 'favorite'));
	}

	public function staffShoppingLimit()
	{
		if ($user = request()->user('api') AND
			$userRole = $user->roles->first() AND
			$userRole->name == 'employee' AND
			$staff = Staff::where('mobile', $user->mobile)->first() AND
			$staff->active_status == 1
		) {
			$orderTable = 'el_order';
			$orderItemTable = 'el_order_item';
			$adjustmentTable = 'el_order_adjustment';
			$used = Order::join($orderItemTable, $orderTable . '.id', '=', $orderItemTable . '.order_id')
				//->join($adjustmentTable, $orderTable . '.id', '=', $adjustmentTable . '.order_id')
				->whereRaw('DATE_ADD(curdate(),interval -day(curdate())+1 day) < ' . $orderTable . '.created_at')//本月
				->where($orderTable . '.type', '=', 2)
				->where($orderTable . '.user_id', '=', $user->id)
				->where($orderTable . '.pay_status', '=', 1)
				->sum($orderItemTable . '.units_total');
			$used = $used / 100;

			$refundTable = 'el_refund';

			$refundAmount = Refund::join($orderTable, $orderTable . '.id', '=', $refundTable . '.order_id')
				->join($orderItemTable, $refundTable . '.order_item_id', '=', $orderItemTable . '.id')
				->whereRaw('DATE_ADD(curdate(),interval -day(curdate())+1 day) < ' . $orderTable . '.pay_time')//本月
				->where($refundTable . '.user_id', '=', $user->id)
				->where($refundTable . '.status', '=', Refund::STATUS_COMPLETE)
				->sum($orderItemTable . '.units_total');

			$refundAmount = $refundAmount / 100;

			$limit = settings('employee_discount_limit') ? settings('employee_discount_limit') : 0;
			$remain = $limit - $used + $refundAmount;
			$used = $used - $refundAmount;

			return $this->api([
				'limit' => $limit,
				'used' => $used,
				'remain' => $remain
			]);
		} else {
			return $this->api(null, false, 403, '无法验证员工身份');
		}
	}

	public function getUserAgentCode()
	{
		$user = request()->user();
		$agent = Agent::where('user_id', $user->id)->where('status', 1)->first();
		if ($agent) {
			return $this->api(['agent_code' => $agent->code]);
		}
		return $this->api(['agent_code' => '']);
	}

	/**
	 * 用户每日签到
	 * @return \Dingo\Api\Http\Response
	 */
	public function dailySign()
	{
		$user = request()->user();
		if (settings('point_enabled')) {

			$sumPointToday = $this->point->getDailySumByAction($user->id, 'daily_sign');

			if ($sumPointToday == 0) {
				$value = settings('daily_sign_point') ? settings('daily_sign_point') : 1;
				$this->point->create(['user_id' => $user->id, 'action' =>
					'daily_sign', 'note' => '签到积分', 'item_type' => User::class,
					'item_id' => $user->id
					, 'value' => $value]);

				event('point.change', $user->id);
				return $this->api(['point' => $value], true, 200, '签到成功');
			}
			return $this->api([], false, 200, '您已签到');
		}
		return $this->api([], false, 500, '签到失败');

	}

	/**
	 * 小程序绑定手机号码
	 * @return \Dingo\Api\Http\Response
	 */
	public function MiniProgramBindMobile()
	{
		$user = request()->user();
		$iv = request('iv');
		$encryptedData = request('encryptedData');

		$app_id = settings('mini_program_app_id');
		$secret = settings('mini_program_secret');
		$code = request('code');

		if (empty($code)) return $this->api([], false, 400, '缺失code');
		$params = [
			'appid' => $app_id,
			'secret' => $secret,
			'js_code' => $code,
			'grant_type' => 'authorization_code'
		];
		$res = $this->Curl('https://api.weixin.qq.com/sns/jscode2session', 'GET', $params);
		if (!isset($res['session_key'])) return $this->api([], false, 400, '获取session_key失败');

		$pc = new WXBizDataCrypt($app_id, $res['session_key']);
		$errCode = $pc->decryptData($encryptedData, $iv, $data);

		if ($errCode == 0) {
			$miniProgramResult = json_decode($data, true);
			\Log::info('bind mobile miniProgramResult:' . json_encode($miniProgramResult));
			$mobile = $miniProgramResult['purePhoneNumber'];
			if ($existUser = $this->user->findWhere(['mobile' => $mobile])->first()) {
				return $this->api(null, false, 400, '此手机号已绑定账号');
			}

			if ($user->mobile) {
				return $this->api(null, false, 400, '已绑定手机号码');
			}

			$user = $this->user->update(['mobile' => $mobile], $user->id);
			event('user.update.mobile', [$user]);
			event('verify_mobile', $user);
			return $this->api([], true, 200, '绑定成功');
		}
		return $this->api([], true, 400, '获取手机号码失败');
	}


	private function Curl($url, $method = 'GET', $params = [], $request_header = [])
	{
		$request_header = ['Content-Type' => 'application/x-www-form-urlencoded'];
		if ($method === 'GET' || $method === 'DELETE') {
			$url .= (stripos($url, '?') ? '&' : '?') . http_build_query($params);
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

	public function bindUserMiniInfo()
	{
//        $miniProgram = EasyWeChat::miniProgram();
		$type = request('app_type');
		$config = [
			'app_id' => settings('mini_program_app_id'),
			'secret' => settings('mini_program_secret')
		];
		if ($type == 'activity') {
			$config = [
				'app_id' => settings('activity_mini_program_app_id'),
				'secret' => settings('activity_mini_program_secret')
			];
		}
		$miniProgram = EasyWeChat\Factory::miniProgram($config);

		//1. get session key.
		$code = request('code');
		$result = $miniProgram->auth->session($code);

		if (!isset($result['session_key'])) {
			return $this->failed('获取 session_key 失败.');
		}

		$sessionKey = $result['session_key'];

		//2. get user info.
		$encryptedData = request('encryptedData');
		$iv = request('iv');

		$decryptedData = $miniProgram->encryptor->decryptData($sessionKey, $iv, $encryptedData);

		$user = request()->user();
		$user->nick_name = $decryptedData['nickName'];
		$user->sex = $decryptedData['gender'] == 1 ? '男' : '女';
		$user->avatar = $decryptedData['avatarUrl'];
		$user->save();

		return $this->success(['user_info' => $user]);
	}
}