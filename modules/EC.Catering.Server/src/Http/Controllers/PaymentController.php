<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016-10-09
 * Time: 18:41
 */

namespace ElementVip\Server\Http\Controllers;

use Carbon\Carbon;
use ElementVip\Activity\Core\Models\Member;
use ElementVip\Activity\Core\Models\PaymentDetail;
use GuoJiangClub\Catering\Component\Order\Models\Order;
use GuoJiangClub\Catering\Component\Order\Models\OrderItemPoint;
use ElementVip\Component\Payment\Contracts\PaymentChargeContract;
use GuoJiangClub\Catering\Component\Payment\Models\PaymentLog;
use ElementVip\Component\Point\Model\Point;
use ElementVip\Component\Point\Repository\PointRepository;
use ElementVip\Component\Product\Models\Goods;
use ElementVip\Component\Product\Models\Product;
use ElementVip\Component\Order\Processor\OrderProcessor;
use ElementVip\Component\Order\Repositories\OrderRepository;
use GuoJiangClub\Catering\Component\Payment\Models\Payment;
use ElementVip\Component\Payment\Services\PaymentService;
use ElementVip\Component\Payment\Services\ActivityPaymentService;
use ElementVip\Component\User\Models\UserBind;
use ElementVip\Server\Transformers\OrdersTransformer;
use iBrand\Component\MultiGroupon\Models\MultiGrouponUsers;
use Pingpp\WxpubOAuth;
use Illuminate\Events\Dispatcher;
use ElementVip\Activity\Core\Repository\MemberRepository;
use ElementVip\Component\Balance\Repository\BalanceRepository;
use ElementVip\Activity\Core\Repository\PaymentRepository;
use ElementVip\Activity\Core\Models\Activity;
use ElementVip\Notifications\PointRecord;
use ElementVip\Component\User\Models\User;
use DB;

class PaymentController extends Controller
{
    private $payment;
    private $orderRepository;
    private $orderProcessor;
    private $pointRepository;
    private $events;
    private $balanceRepository;
    private $activityPayment;
    private $member;
    private $paymentRepository;
    private $charge;

    public function __construct(PaymentService $paymentService
        , OrderRepository $orderRepository
        , OrderProcessor $orderProcessor
        , PointRepository $pointRepository
        , Dispatcher $events
        , BalanceRepository $balanceRepository
        , ActivityPaymentService $activityPaymentService
        , MemberRepository $memberRepository
        , PaymentRepository $paymentRepository
        , PaymentChargeContract $charge
    )
    {
        $this->payment = $paymentService;
        $this->orderRepository = $orderRepository;
        $this->orderProcessor = $orderProcessor;
        $this->pointRepository = $pointRepository;
        $this->events = $events;
        $this->balanceRepository = $balanceRepository;
        $this->activityPayment = $activityPaymentService;
        $this->member = $memberRepository;
        $this->paymentRepository = $paymentRepository;
        $this->charge = $charge;
    }

    public function createCharge()
    {
        $user = request()->user();

        $order_no = request('order_no');

        if (!$order_no || !$order = $this->orderRepository->getOrderByNo($order_no)) {
            return $this->api(null, false, 400, '订单不存在');
        }

        if ($user->cant('pay', $order)) {
            return $this->api(null, false, 400, '无权操作此订单');
        }

        if ($order->status == Order::STATUS_INVALID) {
            return $this->api(null, false, 400, '无法支付');
        }

        if ($order->getNeedPayAmount() == 0 && request('balance')) {
            return $this->api(['type' => 'balance'], true, 200, '');
        }

        if ($order->getNeedPayAmount() === 0) {
            return $this->api(null, false, 400, '无法支付，需支付金额为零');
        }

        $redirect_url = $this->getPayRedirectUrl();

        //余额已支付
        if ($balance = request('balance') AND is_numeric($balance) AND $balance > 0
            /*AND $order->getNeedPayAmount() - $order->total == 0*/
        ) {

            $balance = $balance * 100;

            $userBalance = $this->balanceRepository->getSum($user->id);

            if ($userBalance <= 0 OR $userBalance < $balance) {
                return $this->api([], false, 400, '余额不足');
            }

            //计算出余额能支付的金额，余额大于整个订单金额，则支付订单金额，如果小于，则支付余额
            $total = $order->getNeedPayAmount() <= $balance ? $order->getNeedPayAmount() : $balance;

            try {

                DB::beginTransaction();

                $payment = new Payment(['order_id' => $order->id, 'channel' => 'balance',
                    'amount' => $total, 'status' => Payment::STATUS_COMPLETED
                    , 'paid_at' => Carbon::now()]);

                $order->payments()->save($payment);

                $this->balanceRepository->addRecord(
                    ['user_id' => $user->id,
                        'type' => 'order_payment',
                        'note' => '订单余额支付：' . $total/100 . ',订单号：' . $order->order_no,
                        'value' => -$total,
                        'origin_id' => $payment->id,
                        'origin_type' => Payment::class,
                    ]);

                DB::commit();
            } catch (\Exception $exception) {

                DB::rollBack();

                \Log::info($exception->getMessage() . $exception->getTraceAsString());

                return $this->api(null, false, 400, '余额支付失败');
            }

            $order = $this->orderRepository->getOrderByNo($order_no);

            //标明余额完成了所有的支付金额
            if ($order->getNeedPayAmount() == 0) {
                /*return $this->api(['type' => 'balance'], true, 200, '余额支付成功');*/
                $name = 'balance';

                if (request('channel') == 'wx_lite') {
                    return $this->api(compact('name'));
                } else {
                    $redirect_url = settings('wechat_pay_success_url') . $order_no;

                    return $this->api(compact('redirect_url'));
                }
            }
        }

        if (request('channel') == 'wx_pub_qr') {
            $charge = $this->charge->createCharge($order->user_id
                , request('channel')
                , 'order'
                , $order_no
                , $order->getNeedPayAmount()
                , $order->getSubject()
                , $order->getSubject()
                , request()->getClientIp()
                , ''
                , request('extra')
                , $order->submit_time);

            return $this->api(compact('charge'));
        }

        if (request('channel') == 'wx_lite') {

            $name = $this->charge->getName();

            $charge = $this->charge->createCharge($order->user_id
                , request('channel')
                , 'order'
                , $order_no
                , $order->getNeedPayAmount()
                , $order->getSubject()
                , $order->getSubject()
                , request()->getClientIp()
                , request('openid')
                , request('extra')
                , $order->submit_time);

            return $this->api(compact('charge', 'name'));
        }

        return $this->api(compact('redirect_url'));
    }

    public function paidSuccess()
    {
        $user = request()->user();
        $order_no = request('order_no');

        if (!$order_no || !$order = $this->orderRepository->getOrderByNo($order_no)) {
            return $this->response()->errorBadRequest('订单不存在');
        }

        if ($user->cant('update', $order)) {
            return $this->response()->errorForbidden('You have no right to operate this order.');
        }

        $need_pay = $order->getNeedPayAmount();

        $pay_state = request('amount') * 100 - $need_pay;

        if (settings('pingxx_pay_scene') == 'test' AND $pay_state >= 0) {

            $payment = new Payment(['order_id' => $order->id, 'channel' => request('channel') ? request('channel') : 'test',
                'amount' => request('amount') ? request('amount') : $order->total, 'status' => Payment::STATUS_COMPLETED
                , 'paid_at' => Carbon::now()]);

            $order = $this->orderRepository->getOrderByNo($order_no);

            $order->payments()->save($payment);

            event('order.customer.paid', [$order]);

            event('order.seckill.sell.num', [$order]);

            $this->orderProcessor->process($order);
        }

        if ($order->balance_paid) {
            $this->orderProcessor->process($order);
        }

        $multiGroupon = '';
        if ($order->type == Order::TYPE_MULTI_GROUPON) {
            $multiGroupon = MultiGrouponUsers::where('order_id', $order->id)->first();
        }
        $order->multiGroupon = $multiGroupon;

        /*再次校验订单支付状态*/
        if ($order->status == Order::STATUS_NEW) {
            $result = $this->charge->queryByOutTradeNumber($order_no);
            if (count($result) > 0 AND $result['metadata']['type'] == 'order') {
                $this->payment->paySuccess($result);
                $order = $this->orderRepository->getOrderByNo($order_no);
            }
        }

        if ($order->status == Order::STATUS_PAY) {
            $pointInfo = $this->getPointInfo($order);

            /*$this->events->fire('order.erp.store',[$order]);
            $this->events->fire('order.erp.payment', [$order]);*/

            return $this->api(['order' => $order, 'pointInfo' => $pointInfo, 'payment' => $this->getPayment($order)]);
        }
        return $this->api($order, true);
    }

    /**
     * 弃用的老方法
     * @return \Dingo\Api\Http\Response
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function activityPaidSuccess()
    {
        $order_no = request('order_no');
        $order = $this->member->with('activity')->findWhere(['order_no' => $order_no])->first();
        if (!$order_no || count($order) <= 0) {
            return $this->api([], false, 500, '订单不存在');
        }

        if (settings('pingxx_pay_scene') == 'test' && $order->status == 0 && $order->pay_status == 0) {
            if (!PaymentDetail::where('pingxx_no', 'XXXXXXXXXX')->where('order_id', $order->id)->first()) {
                PaymentDetail::create([
                    'order_id' => $order->id,
                    'channel' => 'alipay_wap',
                    'amount' => '100',
                    'status' => PaymentDetail::STATUS_COMPLETED,
                    'channel_no' => 'XXXXXXXXXX',
                    'pingxx_no' => 'XXXXXXXXXX',
                    'paid_at' => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }

            $activity = Activity::find($order->activity_id);
            $payment = $this->paymentRepository->find($order->payment_id);
            $user = User::find($order->user_id);
            if ($payment->type == 2) {
                $this->pointRepository->create([
                    'user_id' => $user->id,
                    'action' => 'activity',
                    'note' => '活动报名',
                    'value' => (-1) * $payment->point,
                    'valid_time' => 0,
                    'item_type' => Payment::class,
                    'item_id' => $payment->id,
                ]);
                event('point.change', $user->id);
                $user->notify(new PointRecord(['point' => [
                    'user_id' => $user->id,
                    'action' => 'activity',
                    'note' => '活动报名',
                    'value' => $payment->point,
                    'valid_time' => 0,
                    'item_type' => Payment::class,
                    'item_id' => $payment->id,
                ]]));
            }

            if ($payment->type == 1 || $payment->type == 2) {
                $activity->update(['member_count' => $activity->member_count + 1]);
                if ($payment->limit > 0 && $payment->is_limit == 1) {
                    $payment->update(['limit' => $payment->limit - 1]);
                }
            }

            $this->member->update(['status' => 1, 'pay_status' => 1], $order->id);
        }

        /*再次校验订单支付状态*/
        if ($order->status == 0 AND $order->pay_status == 0) {
            $result = $this->charge->queryByOutTradeNumber($order_no);
            if (count($result) > 0 AND $result['metadata']['type'] == 'activity') {
                $this->activityPayment->paySuccess($result);
                $order = $this->member->with('activity')->findWhere(['order_no' => $order_no])->first();
            }
        }

        return $this->api($order, true);
    }

    private function getPointInfo($order)
    {
        $pointUsed = Point::where([
            'item_type' => 'GuoJiangClub\Catering\Component\Order\Models\Order',
            'item_id' => $order->id,
        ])->first();
        $pointUsed = $pointUsed ? $pointUsed->value : 0;
        $pointAdded = 0;
        $items = $order->getItems();
        foreach ($items as $item) {
            if ($item->units_total != 0) {
                $point = Point::where([
                    'item_type' => 'GuoJiangClub\Catering\Component\Order\Models\OrderItem',
                    'item_id' => $item->id,
                ])->first();
                if ($point) {
                    $pointAdded += $point->value;
                }
            }
        }
        $pointTotal = $this->pointRepository->getSumPointValid($order->user_id, 'default');

        return [
            'pointUsed' => $pointUsed,
            'pointAdded' => $pointAdded,
            'pointTotal' => $pointTotal,
        ];
    }

    private function getPayment($order)
    {
        $payment = $order->payments->last();

        if (!$payment) {
            return '积分支付';
        }

        $payment->channel = $payment->channel == 'test' ? '测试'
            : ($payment->channel == 'alipay_wap' ? '支付宝'
                : ($payment->channel == 'wx_pub' ? '微信'
                    : $payment->channel));

        return $payment;
    }

    private function getPayRedirectUrl()
    {
        $type = 'order';
        $order_no = request('order_no');
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