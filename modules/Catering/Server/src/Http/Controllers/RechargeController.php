<?php

namespace GuoJiangClub\Catering\Server\Http\Controllers;

use ElementVip\Component\Balance\Model\Balance;
use ElementVip\Component\Recharge\Models\BalanceOrder;
use ElementVip\Component\Payment\Contracts\PaymentChargeContract;
use ElementVip\Component\Recharge\Repositories\RechargeRuleRepository;
use GuoJiangClub\Catering\Server\Service\NotifyService;
use Illuminate\Http\Request;
use Validator;
use EasyWeChat;

class RechargeController extends Controller
{
	protected $rechargeRuleRepository;
	protected $chargeContract;
	protected $notifyService;

	public function __construct(RechargeRuleRepository $rechargeRuleRepository, PaymentChargeContract $chargeContract, NotifyService $notifyService)
	{
		$this->rechargeRuleRepository = $rechargeRuleRepository;
		$this->chargeContract         = $chargeContract;
		$this->notifyService          = $notifyService;
	}

	public function charge(Request $request)
	{
		$input = $request->except('_token');
		$rules = [
			'code'             => 'required',
			'recharge_rule_id' => 'required|integer|min:1',
			'amount'           => 'required|integer|min:1',
			'pay_amount'       => 'required|integer|min:1',
			'channel'          => 'required|in:wx_lite',
		];

		$message = [
			"required" => ":attribute 不能为空",
			"integer"  => ":attribute 必须为整数",
			"min"      => "错误的 :attribute",
			"in"       => "错误的 :attribute",
		];

		$attributes = [
			'recharge_rule_id' => '充值方案',
			'amount'           => '充值金额',
			'pay_amount'       => '支付金额',
			'channel'          => '充值方式',
			'code'             => 'code凭证',
		];

		$validator = Validator::make($input, $rules, $message, $attributes);
		if ($validator->fails()) {
			$warnings     = $validator->messages();
			$show_warning = $warnings->first();

			return $this->failed($show_warning);
		}

		if ($input['amount'] < $input['pay_amount']) {
			return $this->failed('支付金额错误');
		}

		$rule = $this->rechargeRuleRepository->findWhere(['id' => $input['recharge_rule_id'], 'status' => 1])->first();
		if (!$rule) {
			return $this->failed('充值方案错误');
		}

		if (intval($rule->amount) != $input['amount'] || intval($rule->payment_amount) != $input['pay_amount']) {
			return $this->failed('支付金额错误');
		}

		$miniProgram = EasyWeChat::miniProgram('shitang');
		$result      = $miniProgram->auth->session($input['code']);
		if (!isset($result['openid'])) {
			return $this->failed('获取openid失败.');
		}

		$user    = request()->user();
		$subject = '充值';
		$body    = '充值';

		$payConfig = settings('shitang_miniProgram_pay_config');
		$order_no  = $payConfig['msgSrcId'] . date('YymdHis') . mt_rand(10000000, 99999999);
		$order     = BalanceOrder::create([
			'user_id'          => $user->id,
			'order_no'         => $order_no,
			'pay_type'         => $input['channel'],
			'amount'           => $input['amount'],
			'pay_amount'       => $input['pay_amount'],
			'recharge_rule_id' => $input['recharge_rule_id'],
		]);

		if ('wx_lite' == $input['channel']) {
			$charge = $this->chargeContract->createCharge($user->id, 'wx_lite', 'recharge', $order->order_no, $order->pay_amount, $subject, $body, request()->getClientIp(), $result['openid'], '');
			if (!empty($charge)) {
				return $this->success($charge);
			}
		}

		return $this->failed('充值失败');
	}

	public function paidSuccess($order_no)
	{
		$order = BalanceOrder::where('order_no', $order_no)->first();
		if (!$order) {
			$this->failed('订单不存在');
		}

		if ($order->pay_status == 0) {
			$result = $this->chargeContract->queryByOutTradeNumber($order_no);
			if (!empty($result) AND $result['attachedData']['type'] == 'recharge') {
				$this->notifyService->notify($order_no, $result, $result['attachedData']);
				$order = BalanceOrder::where('order_no', $order_no)->first();
			}
		}

		if ($order->pay_status == 1) {
			event('st.on.balance.changed', [$order, request('formId')]);
			//, request('formId')
            event('st.wechat.message.balance', [$order]);
		}

		$balance      = Balance::SumByUser($order->user_id);
		$prev_balance = Balance::where('origin_id', '!=', $order->id)->where('user_id', $order->user_id)->sum('value');

		return $this->success(['order' => $order, 'current_balance' => $balance, 'prev_balance' => $prev_balance]);
	}
}