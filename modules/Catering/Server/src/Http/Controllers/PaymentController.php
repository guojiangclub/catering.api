<?php

namespace GuoJiangClub\Catering\Server\Http\Controllers;

use GuoJiangClub\Catering\Component\Order\Models\Order;
use GuoJiangClub\Catering\Component\Payment\Contracts\PaymentChargeContract;
use GuoJiangClub\Catering\Component\Point\Model\Point;
use GuoJiangClub\Catering\Component\Point\Repository\PointRepository;
use GuoJiangClub\Catering\Component\Order\Processor\OrderProcessor;
use GuoJiangClub\Catering\Component\Order\Repositories\OrderRepository;
use GuoJiangClub\Catering\Component\Payment\Services\PaymentService;
use Illuminate\Events\Dispatcher;
use GuoJiangClub\Catering\Component\Balance\Repository\BalanceRepository;

class PaymentController extends Controller
{
    private $payment;
    private $orderRepository;
    private $orderProcessor;
    private $pointRepository;
    private $events;
    private $balanceRepository;
    private $charge;

    public function __construct(PaymentService $paymentService
        , OrderRepository $orderRepository
        , OrderProcessor $orderProcessor
        , PointRepository $pointRepository
        , Dispatcher $events
        , BalanceRepository $balanceRepository
        , PaymentChargeContract $charge
    )
    {
        $this->payment           = $paymentService;
        $this->orderRepository   = $orderRepository;
        $this->orderProcessor    = $orderProcessor;
        $this->pointRepository   = $pointRepository;
        $this->events            = $events;
        $this->balanceRepository = $balanceRepository;
        $this->charge            = $charge;
    }

    public function paidSuccess()
    {
        $user     = request()->user();
        $order_no = request('order_no');

        if (!$order_no || !$order = $this->orderRepository->getOrderByNo($order_no)) {
            return $this->failed('订单不存在');
        }

        if ($user->cant('update', $order)) {
            return $this->failed('You have no right to operate this order.');
        }

        if ($order->balance_paid) {
            $this->orderProcessor->process($order);
        }

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

            return $this->success(['order' => $order, 'pointInfo' => $pointInfo, 'payment' => $this->getPayment($order)]);
        }

        return $this->success($order);
    }

    private function getPointInfo($order)
    {
        $pointUsed  = Point::where([
            'item_type' => 'GuoJiangClub\Catering\Component\Order\Models\Order',
            'item_id'   => $order->id,
        ])->first();
        $pointUsed  = $pointUsed ? $pointUsed->value : 0;
        $pointAdded = 0;
        $items      = $order->getItems();
        foreach ($items as $item) {
            if ($item->units_total != 0) {
                $point = Point::where([
                    'item_type' => 'GuoJiangClub\Catering\Component\Order\Models\OrderItem',
                    'item_id'   => $item->id,
                ])->first();
                if ($point) {
                    $pointAdded += $point->value;
                }
            }
        }
        $pointTotal = $this->pointRepository->getSumPointValid($order->user_id, 'default');

        return [
            'pointUsed'  => $pointUsed,
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
}