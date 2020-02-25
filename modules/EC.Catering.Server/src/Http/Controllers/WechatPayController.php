<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016-12-06
 * Time: 16:11
 */

namespace GuoJiangClub\EC\Catering\Server\Http\Controllers;

use Carbon\Carbon;
use ElementVip\Component\Balance\Model\BalanceOrder;
use ElementVip\Component\Balance\Repository\BalanceRepository;
use GuoJiangClub\Catering\Component\Order\Models\Order;
use ElementVip\Component\Order\Processor\OrderProcessor;
use ElementVip\Component\Order\Repositories\OrderRepository;
use ElementVip\Component\Payment\Contracts\PaymentChargeContract;
use GuoJiangClub\Catering\Component\Payment\Models\Payment;
use ElementVip\Component\Payment\Services\PaymentService;
use ElementVip\Component\User\Models\User;
use ElementVip\Component\User\Models\UserBind;
use Illuminate\Http\Request;
use Pingpp\WxpubOAuth;
use ElementVip\Activity\Core\Models\Member;
use ElementVip\Activity\Core\Repository\MemberRepository;
use ElementVip\Component\Payment\Services\ActivityPaymentService;
use ElementVip\Activity\Core\Repository\ActivityRepository;
use DB;

class WechatPayController extends Controller
{
    private $payment;
    private $activityPayment;
    private $orderRepository;
    private $orderProcessor;
    private $member;
    private $activity;
    private $balanceRepository;
    private $charge;

    public function __construct(PaymentService $paymentService
        , OrderRepository $orderRepository
        , OrderProcessor $orderProcessor
        , MemberRepository $memberRepository
        , ActivityPaymentService $activityPaymentService
        , ActivityRepository $activityRepository
        , BalanceRepository $balanceRepository
        , PaymentChargeContract $paymentChargeContract)
    {
        $this->payment = $paymentService;
        $this->orderRepository = $orderRepository;
        $this->orderProcessor = $orderProcessor;
        $this->member = $memberRepository;
        $this->activityPayment = $activityPaymentService;
        $this->activity = $activityRepository;
        $this->balanceRepository = $balanceRepository;
        $this->charge = $paymentChargeContract;
    }

    public function getChargeView()
    {
        if (settings('enabled_pingxx_pay')) {
            return 'server::payment.pingxxWxPay';
        }

        return 'server::payment.defaultWxPay';
    }

    /**
     * 微信支付获取code
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function getCode(Request $request)
    {
        $channel = request('channel');
        $type = request('type');
        $amount = request('amount');
        $order_no = request('order_no');
        $balance = request('balance');

        //订单支付
        $url = route('wechat.pay.wxPay', ['channel' => $channel, 'type' => $type, 'order_no' => $order_no, 'balance' => $balance]);
        return redirect(WxpubOAuth::createOauthUrlForCode(settings('wechat_pay_app_id'), $url));

        /*if ($type == 'recharge') {
            $order = BalanceOrder::where('order_no', $order_no)->first();
        } elseif ($type == 'activity') {
            $order = $this->member->with('payment')->findWhere(['order_no' => $order_no])->first();
        } else {
            $order = $this->orderRepository->getOrderByNo($order_no);
        }



        if ($order AND $order->user_id) {
            $userBind = UserBind::ByAppID($order->user_id, 'wechat', settings('wechat_pay_app_id'))->first();
        }

        if ($userBind AND $userBind->open_id) {
            $url = route('wechat.pay.wxPay', ['channel' => $channel, 'type' => $type, 'order_no' => $order_no, 'balance' => $balance, 'open_id' => $userBind->open_id]);
            return redirect($url);
        } else {
            //订单支付
            $url = route('wechat.pay.wxPay', ['channel' => $channel, 'type' => $type, 'order_no' => $order_no, 'balance' => $balance]);
            return redirect(WxpubOAuth::createOauthUrlForCode(settings('wechat_pay_app_id'), $url));
        }*/

    }

    public function wxPay()
    {
        $channel = request('channel');
        $type = request('type');
        $amount = request('amount');
        $order_no = request('order_no');
        $code = request('code');
        $balance = request('balance');

        if (!$openid = request('open_id')) {
            $openid = WxpubOAuth::getOpenid(settings('wechat_pay_app_id'), settings('wechat_pay_app_secret'), $code);
        }

        list($failUrl, $failUcenter, $successUrl) = $this->getReturnUrl($type, $order_no);

        /*if (settings('enabled_pingxx_pay')) {
            //订单支付
            return view('server::payment.wxPay', compact('channel', 'type', 'amount', 'openid', 'order_no', 'failUrl', 'failUcenter', 'successUrl', 'balance'));
        } else {
            return redirect(route('wechat.pay.get.charge', compact('channel', 'type', 'amount', 'openid', 'order_no', 'failUrl', 'failUcenter', 'successUrl', 'balance')));
            // return view('server::payment.ibrandPay', compact('channel', 'type', 'amount', 'openID', 'order_no', 'failUrl', 'failUcenter', 'successUrl', 'balance'));
        }*/

        if ($type == 'activity') {
            return redirect(route('activity.wechat.pay.get.charge', compact('channel', 'type', 'amount', 'openid', 'order_no', 'failUrl', 'failUcenter', 'successUrl', 'balance')));
        }

        return redirect(route('wechat.pay.get.charge', compact('channel', 'type', 'amount', 'openid', 'order_no', 'failUrl', 'failUcenter', 'successUrl', 'balance')));
    }

    public function createCharge()
    {

        $order_no = request('order_no');
        $openid = request('openid');
        $successUrl = request('successUrl');
        $failUrl = request('failUrl');
        $failUcenter = request('failUcenter');

        $channel = request('channel');
        $ip = request()->getClientIp();
        $extra = request('extra');
        $type = request('type');
        $balance = \request('balance');

        if (request('type') == 'recharge') {

            $order = BalanceOrder::where('order_no', $order_no)->first();

            $userId = $order->user_id;
            $amount = $order->pay_amount;
            $subject = '余额充值';
            $body = '余额充值';

            $charge = $this->charge->createCharge($userId, $channel, $type, $order_no, $amount, $subject, $body, $ip, $openid, $extra);


            return view($this->getChargeView(), compact('charge', 'successUrl', 'failUrl', 'failUcenter', 'balance'));

        }

        if (!$order_no || !$order = $this->orderRepository->getOrderByNo($order_no)) {
            return $this->response()->errorBadRequest('订单不存在');
        }

        if ($order->getNeedPayAmount() == 0) {
            return redirect($failUcenter);
        }

        $charge = $this->charge->createCharge(
            $order->user_id
            , request('channel')
            , 'order'
            , $order_no
            , $order->getNeedPayAmount()
            , $order->getSubject()
            , $order->getSubject()
            , request()->getClientIp()
            , $openid
            , request('extra')
        );

        return view($this->getChargeView(), compact('charge', 'successUrl', 'failUrl', 'failUcenter', 'balance'));
    }

    public function createActivityCharge()
    {
        $successUrl = request('successUrl');
        $failUrl = request('failUrl');
        $failUcenter = request('failUcenter');

        $order_no = request('order_no');
        $openid = request('openid');
        $order = $this->member->with('payment')->findWhere(['order_no' => $order_no])->first();
        if (!$order_no || count($order) <= 0) {

            return $this->api([], false, 500, '订单不存在');
        }

        if ($order->status == Member::STATUS_INVALID) {
            return $this->api(null, false, 400, '无法支付');
        }

        if (!isset($order->price) || $order->price === 0) {
            return $this->api(null, false, 400, '无法支付,需支付金额为零');
        }

        $activity = $this->activity->find($order->activity_id);
        $charge = $this->charge->createCharge(
            $order->user_id
            , request('channel')
            , 'activity'
            , $order_no
            , $order->price
            , $activity->id . ' ' . $activity->title
            , $activity->id . ' ' . $activity->title
            , request()->getClientIp()
            , $openid
            , request('extra')
        );

        return view($this->getChargeView(), compact('charge', 'successUrl', 'failUrl', 'failUcenter', 'balance'));

        //return response()->json($charge);
    }

    /**
     * @param $type
     * @param $order_no
     * @return array
     */
    private function getReturnUrl($type, $order_no)
    {
        if ($type == 'recharge') {
            $failUrl = settings('recharge_wechat_pay_fail_url');
            $failUcenter = settings('recharge_wechat_pay_fail_ucenter');
            $successUrl = settings('recharge_wechat_pay_success_url');
        } elseif ($type == 'activity') {
            $failUrl = settings('wechat_activity_pay_fail_url');
            $failUcenter = settings('wechat_activity_pay_fail_ucenter');
            $successUrl = settings('wechat_activity_pay_success_url');
            $failUrl = $failUrl . '?out_trade_no=' . $order_no;
            $failUcenter = $failUcenter . '?out_trade_no=' . $order_no;
            $successUrl = $successUrl . '?out_trade_no=' . $order_no;
        } else {
            $failUrl = settings('wechat_pay_fail_url');
            $failUcenter = settings('wechat_pay_fail_ucenter');
            $successUrl = settings('wechat_pay_success_url');
        }
        return array($failUrl, $failUcenter, $successUrl);
    }
}