<?php

namespace GuoJiangClub\EC\Catering\Server\Http\Controllers;

use GuoJiangClub\Catering\Component\Balance\Model\BalanceOrder;
use GuoJiangClub\Catering\Component\Payment\Contracts\PaymentChargeContract;
use GuoJiangClub\Catering\Component\Point\Repository\PointRepository;
use GuoJiangClub\Catering\Component\Order\Processor\OrderProcessor;
use GuoJiangClub\Catering\Component\Order\Repositories\OrderRepository;
use GuoJiangClub\Catering\Component\Payment\Services\PaymentService;
use Illuminate\Events\Dispatcher;
use GuoJiangClub\Catering\Component\Balance\Repository\BalanceRepository;

class AliPayController extends Controller
{
    private $payment;
    private $orderRepository;
    private $orderProcessor;
    private $pointRepository;
    private $events;
    private $balanceRepository;
    private $member;
    private $paymentRepository;
    private $charge;

    public function __construct(PaymentService $paymentService
        , OrderRepository $orderRepository
        , OrderProcessor $orderProcessor
        , PointRepository $pointRepository
        , Dispatcher $events
        , BalanceRepository $balanceRepository
        , PaymentRepository $paymentRepository
        , PaymentChargeContract $paymentChargeContract
    )
    {
        $this->payment           = $paymentService;
        $this->orderRepository   = $orderRepository;
        $this->orderProcessor    = $orderProcessor;
        $this->pointRepository   = $pointRepository;
        $this->events            = $events;
        $this->balanceRepository = $balanceRepository;
        $this->paymentRepository = $paymentRepository;
        $this->charge            = $paymentChargeContract;
    }

    public function createCharge()
    {
        $order_no = request('order_no');

        if (request('type') == 'recharge') {

            $order = BalanceOrder::where('order_no', $order_no)->first();

            $charge = $this->charge->createCharge($order->user_id, request('channel'), 'recharge', $order->order_no, $order->pay_amount,
                '余额充值', '余额充值', request()->getClientIp(), '', request('extra'));
        } else {

            if (!$order_no || !$order = $this->orderRepository->getOrderByNo($order_no)) {
                return $this->response()->errorBadRequest('订单不存在');
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
                , ''
                , request('extra')
            );
        }

        if (settings('enabled_pingxx_pay')) {
            return view('server::payment.pingxxAliPay', compact('charge'));
        }

        return view('server::payment.defaultAliPay', compact('charge'));
    }

}