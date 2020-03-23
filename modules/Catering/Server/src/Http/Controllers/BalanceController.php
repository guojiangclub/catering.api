<?php

namespace GuoJiangClub\Catering\Server\Http\Controllers;

use Carbon\Carbon;
use GuoJiangClub\Catering\Component\Balance\Model\Balance;
use GuoJiangClub\Catering\Component\Balance\Model\BalanceCash;
use GuoJiangClub\Catering\Component\Balance\Model\BalanceOrder;
use GuoJiangClub\Catering\Component\Balance\Repository\BalanceRepository;
use GuoJiangClub\Catering\Component\Payment\Contracts\PaymentChargeContract;
use GuoJiangClub\Catering\Component\Payment\Services\PaymentService;
use GuoJiangClub\Catering\Server\Transformers\BalanceCashTransformer;
use GuoJiangClub\Catering\Server\Transformers\BalanceTransformer;
use GuoJiangClub\Catering\Component\Recharge\Repositories\RechargeRuleRepository;

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

        return $this->success(compact('sum'));
    }

    public function getSchemes()
    {
        $lists = $this->rechargeRuleRepository->getEffective();

        return $this->success($lists);
    }

    public function charge()
    {
        $user    = request()->user();
        $channel = request('channel');
        if (!$this->rechargeRuleRepository->getEffectiveByID(request('recharge_rule_id'), request('pay_amount'), request('amount'))) {
            return $this->failed('储值优惠不存在');
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

            return $this->success(compact('charge'));
        }

        if (request('channel') == 'wx_lite') {

            $name = $this->charge->getName();

            $charge = $this->charge->createCharge($userId, $channel, 'recharge', $order_no, $amount, $subject, $body, request()->getClientIp(), request('openid'), request('extra'));

            return $this->success(compact('charge', 'name'));
        }

        return $this->success(compact('redirect_url'));
    }

    public function paid()
    {
        $user     = request()->user();
        $order_no = request('order_no');
        if (!$order_no || !$order = BalanceOrder::where('order_no', $order_no)->with('recharge')->first()) {
            return $this->failed('订单不存在');
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
                /*$user = User::find($order->user_id);
                $user->notify(new ChargeSuccess(['charge' => ['user_id' => $order->user_id, 'type' => 'recharge', 'note' => '充值', 'value' => $order->amount, 'origin_id' => $order->id, 'origin_type' => BalanceOrder::class]]));*/
                $sum = $sum + $order->amount;
            }
        }

        event('user.recharge.point', [$order]);
        event('user.recharge.coupon', [$order]);

        return $this->success(compact('order', 'sum'));
    }

    /**
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