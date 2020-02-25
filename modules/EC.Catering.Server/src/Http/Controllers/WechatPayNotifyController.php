<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016-12-06
 * Time: 16:11
 */

namespace GuoJiangClub\EC\Catering\Server\Http\Controllers;

use Carbon\Carbon;
use ElementVip\Component\Payment\Contracts\PaymentChargeContract;
use ElementVip\Component\Payment\Services\ActivityPaymentService;
use ElementVip\Component\Payment\Services\PaymentService;
use Yansongda\Pay\Pay;

class WechatPayNotifyController extends Controller
{

    protected $pay;
    protected $payment;
    protected $activityPayment;

    public function __construct(PaymentChargeContract $pay, PaymentService $paymentService, ActivityPaymentService $activityPaymentService)
    {
        $this->pay = $pay;
        $this->payment = $paymentService;
        $this->activityPayment = $activityPaymentService;
    }


    protected function notify($type)
    {

        switch ($type) {
            case 'wx':
                $config = $this->pay->getConfig('wechat');
                break;
            case 'wx_lite':
                $config = $this->pay->getConfig('miniapp');
                break;
            default:
                $config = [];
        }

        $pay = Pay::wechat($config);

        $data = $pay->verify(); // 是的，验签就这么简单！

        if ('SUCCESS' === $data['return_code']) { // return_code 表示通信状态，不代表支付状态
            $attach = json_decode($data['attach'], true);
            if ($data['result_code'] == "SUCCESS") {

                /*if ($attach['channel'] == 'wx_lite') {
                    $wechat_order = $pay->find(['out_trade_no' => $data->out_trade_no, 'type' => 'miniapp']);
                } else {
                    $wechat_order = $pay->find(['out_trade_no' => $data->out_trade_no]);
                }

                $order = $wechat_order->toArray();

                $dataNew = [
                    'type' => $attach['type'],
                    'channel' => $attach['channel'],
                    'out_trade_no' => $attach['order_sn'],
                    'total_amount' => $order['cash_fee'] / 100,
                    'trade_no' => $order['transaction_id'],
                    'send_pay_date' => date('Y-m-d H:i:s', strtotime($order['time_end'])),
                ];
                $this->pay->paySuccess($dataNew);*/

                $charge['metadata']['order_sn'] = $attach['order_sn'];
                $charge['metadata']['type'] = $attach['type'];
                $charge['amount'] = $data['total_fee'];
                $charge['transaction_no'] = $data['transaction_id'];
                $charge['channel'] = $attach['channel'];
                $charge['id'] = $data['out_trade_no'];
                $charge['time_paid'] = strtotime($data['time_end']);
                $charge['details'] = json_encode($data);

                $this->pay->createPaymentLog('result_pay', Carbon::createFromTimestamp(strtotime($data['time_end'])), $attach['order_sn'], $data['out_trade_no'], $data['transaction_id'], $data['total_fee'], $attach['channel'], $attach['type'], 'SUCCESS', $attach['user_id'], $data);

                if ($attach['type'] == 'activity') {
                    $this->activityPayment->paySuccess($charge);
                } else {
                    $this->payment->paySuccess($charge);
                }
                return $pay->success();
            } elseif ($data['result_code'] == "FAIL") {
                $this->pay->createPaymentLog('result_pay', Carbon::createFromTimestamp(strtotime($data['time_end'])), $attach['order_sn'], $data['out_trade_no'], $data['transaction_id'], $data['total_fee'], $attach['channel'], $attach['type'], 'FAIL', $attach['user_id'], $data);
                return response('支付失败', 500);
            }
        }

        return response('FAIL', 500);

    }


}