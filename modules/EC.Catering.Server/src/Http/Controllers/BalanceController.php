<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/6/21
 * Time: 18:47
 */

namespace ElementVip\Server\Http\Controllers;

use Carbon\Carbon;
use ElementVip\Component\Balance\Model\Balance;
use ElementVip\Component\Balance\Model\BalanceCash;
use ElementVip\Component\Balance\Model\BalanceOrder;
use ElementVip\Component\Balance\Repository\BalanceRepository;
use ElementVip\Component\BankAccount\Model\BankAccount;
use ElementVip\Component\Payment\Contracts\PaymentChargeContract;
use ElementVip\Component\Payment\Services\PaymentService;
use ElementVip\Component\User\Models\User;
use ElementVip\Notifications\MoneyChanged;
use ElementVip\Server\Transformers\BalanceCashTransformer;
use ElementVip\Server\Transformers\BalanceTransformer;

use ElementVip\Notifications\ChargeSuccess;

use  ElementVip\Component\Recharge\Repositories\RechargeRuleRepository;

class BalanceController extends Controller
{

	protected $balanceRepository;
	private   $payment;
	protected $rechargeRuleRepository;
	protected $pay;
	private   $charge;

	public function __construct(PaymentService $paymentService
		, BalanceRepository $balanceRepository
		, RechargeRuleRepository $rechargeRuleRepository
		, PaymentChargeContract $charge)
	{
		$this->payment                = $paymentService;
		$this->balanceRepository      = $balanceRepository;
		$this->rechargeRuleRepository = $rechargeRuleRepository;
		$this->charge                 = $charge;
	}

	public function index()
	{
		return $this->response()->paginator($this->balanceRepository->fluctuation(request()->user()->id)->paginate(), new BalanceTransformer());
	}

	public function sum()
	{
		$user = request()->user();
		$sum  = Balance::sumByUser($user->id);
		if (!is_numeric($sum)) {
			$sum = 0;
		} else {
			$sum = (int) $sum;
		}

		return $this->api(compact('sum'));
	}

	public function getSchemes()
	{
		$lists = $this->rechargeRuleRepository->getEffective();

		return $this->api($lists);
	}

	public function charge()
	{
		$user    = request()->user();
		$channel = request('channel');
		if (!$this->rechargeRuleRepository->getEffectiveByID(request('recharge_rule_id'), request('pay_amount'), request('amount'))) {
			return $this->api([], false, 400, '储值优惠不存在');
		}
		$order = BalanceOrder::create(['user_id'  => $user->id, 'order_no' => build_order_no('R'), 'pay_type' => $channel
		                               , 'amount' => request('amount'), 'pay_amount' => request('pay_amount'), 'recharge_rule_id' => request('recharge_rule_id')]);

		$redirect_url = $this->getRedirectUrl($order->order_no);

		$userId   = $order->user_id;
		$amount   = $order->pay_amount;
		$subject  = '余额充值';
		$body     = '余额充值';
		$order_no = $order->order_no;

		if ($channel == 'wx_pub_qr') {

			$charge = $this->charge->createCharge($userId, $channel, 'recharge', $order_no, $amount, $subject, $body, request()->getClientIp(), '', request('extra'));

			return $this->api(compact('charge'));
		}

		if (request('channel') == 'wx_lite') {

			$name = $this->charge->getName();

			$charge = $this->charge->createCharge($userId, $channel, 'recharge', $order_no, $amount, $subject, $body, request()->getClientIp(), request('openid'), request('extra'));

			return $this->api(compact('charge', 'name'));
		}

		return $this->api(compact('redirect_url'));
		/*if ($channel == 'wx_pub') {
			return $this->api(['redirect_url' => route('wechat.pay.getCode', ['channel' => $channel
				, 'type' => 'recharge', 'order_no' => $order->order_no])]);
		}

		if ($channel == 'alipay_wap') {
			return $this->api($this->payment->createCharge($user->id, $channel, 'recharge', $order->order_no, $order->pay_amount,
				'余额充值', '余额充值', request()->getClientIp(), '', request('extra')));
		}


		$openid = request('openid');

		if ($channel == 'wx_lite' AND !empty($openid)) {
			return $this->api($this->payment->createCharge($user->id, $channel, 'recharge', $order->order_no, $order->pay_amount,
				'余额充值', '余额充值', request()->getClientIp(), $openid, request('extra')));
		}

		return $this->api([], false, 400, '支付参数错误');*/
	}

	public function paid()
	{
		$user     = request()->user();
		$order_no = request('order_no');
		if (!$order_no || !$order = BalanceOrder::where('order_no', $order_no)->with('recharge')->first()) {
			return $this->response()->errorBadRequest('订单不存在');
		}

		$sum = Balance::sumByUser($user->id);
		if (!is_numeric($sum)) {
			$sum = 0;
		} else {
			$sum = (int) $sum;
		}

		if (settings('pingxx_pay_scene') == 'test') {

			if ($order AND $order->pay_status == 0 AND $order->pay_amount == request('pay_amount')) {
				$order->pay_status = 1;
				$order->pay_time   = Carbon::now();
				$order->save();

				Balance::create(['user_id' => $order->user_id, 'type' => 'recharge', 'note' => '充值', 'value' => $order->amount, 'current_balance' => $sum + $order->amount, 'origin_id' => $order->id, 'origin_type' => BalanceOrder::class]);
				$user = User::find($order->user_id);
				$user->notify(new ChargeSuccess(['charge' => ['user_id' => $order->user_id, 'type' => 'recharge', 'note' => '充值', 'value' => $order->amount, 'origin_id' => $order->id, 'origin_type' => BalanceOrder::class]]));
				$sum = $sum + $order->amount;
			}
		}

		event('user.recharge.point', [$order]);
		event('user.recharge.coupon', [$order]);

		return $this->api(compact('order', 'sum'));
	}

	/**
	 * 提现记录列表(因余额与分销佣金账户分开，此接口暂停,移到 ElementVip\Distribution\Server\Http\Controllers\CashController)
	 *
	 * @return \Dingo\Api\Http\Response
	 */
	public function getBalanceCashList()
	{
		$limit = request('limit') ? request('limit') : 15;
		$uid   = request()->user()->id;

		$list = BalanceCash::where('user_id', $uid)->paginate($limit);

		return $this->response()->paginator($list, new BalanceCashTransformer());
	}

	/**
	 * 提交提现申请(因余额与分销佣金账户分开，此接口暂停)
	 *
	 * @return \Dingo\Api\Http\Response|void
	 */
	public function applyBalanceCash()
	{
		$user            = request()->user();
		$amount          = request('amount') * 100;
		$bank_account_id = request('bank_account_id');
		$data            = [
			'amount'          => $amount,
			'bank_account_id' => $bank_account_id,
			'user_id'         => $user->id,
			'status'          => 0,
		];

		$amountCount = $this->balanceRepository->getSum($user->id);
		$balance     = $amountCount - $amount;

		if ($amountCount == 0 OR $balance < 0) {
			return $this->response()->errorBadRequest('提现金额错误');
		}

		if (!$bankAccount = BankAccount::find($bank_account_id)) {
			return $this->response()->errorBadRequest('提现账号错误');
		}

		$data['bank_number'] = $bankAccount->bank_card_number;
		$data['owner_name']  = $bankAccount->owner_name;
		$data['bank_name']   = $bankAccount->bank->bank_name;
		$data['balance']     = $balance;

		$cash = BalanceCash::create($data);

		Balance::create([
			'user_id'         => $user->id,
			'type'            => 'cash',
			'note'            => '余额提现',
			'value'           => -$amount,
			'current_balance' => $balance,
			'origin_id'       => $cash->id,
			'origin_type'     => 'ElementVip\Component\Balance\Model\BalanceCash',
		]);

		$user = User::find($user->id);
		$user->notify(new MoneyChanged(['money' => [
			'user_id'     => $user->id,
			'type'        => 'cash',
			'note'        => '余额提现',
			'value'       => $amount,
			'origin_id'   => $cash->id,
			'origin_type' => 'ElementVip\Component\Balance\Model\BalanceCash',
		]]));

		return $this->api($cash);
	}

	private function getRedirectUrl($order_no)
	{
		$type    = 'recharge';
		$balance = request('balance');

		$channel = request('channel');
		/*if (empty($channel)) {
			$channel = 'wx_pub';
		}*/

		if ($channel == 'alipay_wap') {
			return route('ali.pay.charge', compact('channel', 'type', 'order_no', 'balance'));
		}

		if ($channel == 'wx_pub') {
			return route('wechat.pay.getCode', compact('channel', 'type', 'order_no', 'balance'));
		}
	}

}