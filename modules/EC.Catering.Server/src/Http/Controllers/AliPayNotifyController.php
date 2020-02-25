<?php

namespace GuoJiangClub\EC\Catering\Server\Http\Controllers;

use Carbon\Carbon;
use ElementVip\Component\Payment\Contracts\PaymentChargeContract;
use ElementVip\Component\Payment\Services\ActivityPaymentService;
use ElementVip\Component\Payment\Services\PaymentService;
use Illuminate\Http\Request;
use ElementVip\Component\Payment\Services\PayService;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Log;

class AliPayNotifyController extends Controller
{

    protected $pay;
    protected $charge;
    protected $payment;
    protected $activityPayment;

    public function __construct(PayService $PayService,
                                PaymentService $paymentService,
                                PaymentChargeContract $chargeContract,
                                ActivityPaymentService $activityPaymentService)
    {
        $this->pay             = $PayService;
        $this->charge          = $chargeContract;
        $this->payment         = $paymentService;
        $this->activityPayment = $activityPaymentService;
    }

    public function notify()
    {
        $config = $this->charge->getConfig('alipay');

        $alipay = Pay::alipay($config);

        $data = $alipay->verify(); // 是的，验签就这么简单！

        // 请自行对 trade_status 进行判断及其它逻辑进行判断，在支付宝的业务通知中，只有交易通知状态为 TRADE_SUCCESS 或 TRADE_FINISHED 时，支付宝才会认定为买家付款成功。
        // 1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号；
        // 2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额）；
        // 3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）；
        // 4、验证app_id是否为该商户本身。
        // 5、其它业务逻辑情况

        //\Log::info('支付宝异步通知是否进来');
        //\Log::info($data);

        if ($data['trade_status'] == "TRADE_SUCCESS" || $data['trade_status'] == "TRADE_FINISHED") {

            $attach = json_decode($data['passback_params'], true);

            $charge['metadata']['order_sn'] = $data['out_trade_no'];
            $charge['metadata']['type']     = $attach['type'];
            $charge['amount']               = $data['total_amount'] * 100;
            $charge['transaction_no']       = $data['trade_no'];
            $charge['channel']              = $attach['channel'];
            $charge['id']                   = $data['out_trade_no'];
            $charge['time_paid']            = strtotime($data['gmt_payment']);
            $charge['details']              = json_encode($data);
            $this->charge->createPaymentLog('result_pay', Carbon::createFromTimestamp(strtotime($data['gmt_payment'])), $data['out_trade_no'], $data['out_trade_no'], $data['trade_no'], $data['total_amount'] * 100, $attach['channel'], $attach['type'], 'SUCCESS', $attach['user_id'], $data);

            if ($attach['type'] == 'activity') {
                $this->activityPayment->paySuccess($charge);
            } else {
                $this->payment->paySuccess($charge);
            }

            return $alipay->success();
        }

        return response('alipay notify fail.', 500);
    }

    public function aliReturn($return)
    {
        try {

            $config = $this->charge->getConfig('alipay');

            $alipay = Pay::alipay($config);

            $data = $alipay->verify();

            $return_url = str_replace("~", "/", $return);
            $return_url = str_replace("@", "?", $return_url);
            $return_url = str_replace("*", "#", $return_url);

            //$redirect_url ='';// urldecode($return) . $order_no;

            return redirect($return_url);
        } catch (\Exception $e) {
            \Log::info('支付宝支付失败');
            \Log::info($e->getTraceAsString());
        }

        return redirect(settings('mobile_domain_url'));
    }

    public function returnTest($return_url, $cancel_url)
    {
        $url = url()->full();
        $str = strrchr($url, '?');
        if (settings('pay_scene') != 'test' || !$str) {
            abort(404);
        }

        $title = '已调起ibrand微信模拟支付控件';
        if (request('channel') == 'alipay_wap' || request('channel') == 'alipay_pc_direct') {
            $title = '已调起ibrand支付宝模拟支付控件';
        }
        $post     = route('test.pay.return.success', ['cancel_url' => $cancel_url, 'success_url' => $return_url]);
        $post     .= $str;
        $quit_url = str_replace("~", "/", $cancel_url);
        $quit_url = str_replace("@", "?", $quit_url);
        $quit_url = str_replace("*", "#", $quit_url);

        $html = "<!DOCTYPE html>"
            . "<html lang=\"en\">"
            . "<head>"
            . "   <meta charset=\"utf-8\" />"
            . "   <meta http-equiv=\"cache-control\" content=\"no-cache, no-store, must-revalidate\" />"
            . "   <meta http-equiv=\"pragma\" content=\"no-cache\" />"
            . "   <meta http-equiv=\"expires\" content=\"0\" />"
            . "   <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">"
            . "   <meta name=\"format-detection\" content=\"telephone=no\"/>"
            . "   <meta name=\"format-detection\" content=\"email=no\"/>"
            . "   <meta name=\"viewport\" content=\"width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, shrink-to-fit=no\">"
            . "    <style>"
            . "       *{"
            . "           margin: 0;"
            . "           padding: 0;"
            . "       }"
            . "       html,body{"
            . "           height: 100%;"
            . "       }"
            . "       .contents{"
            . "           width: 100%;"
            . "           height: 100%;"
            . "           overflow: auto;"
            . "           box-sizing: border-box;"
            . "           position: relative;"
            . "           padding: 0 15px;"
            . "           padding-bottom: 30px;"
            . "           background-color: #fff;"
            . "           text-align: center;"
            . "       }"
            . "       .type{"
            . "           padding-top: 120px;"
            . "           font-size: 20px;"
            . "       }"
            . "       .infos{"
            . "           line-height: 30px;"
            . "           padding: 16px 0;"
            . "       }"
            . "       .btn{"
            . "           display: inline-block;"
            . "           margin-bottom: 0;"
            . "           font-weight: normal;"
            . "           text-align: center;"
            . "           vertical-align: middle;"
            . "           cursor: pointer;"
            . "           background-image: none;"
            . "           border: 1px solid transparent;"
            . "           white-space: nowrap;"
            . "           padding: 7px 21px;"
            . "           font-size: 16px;"
            . "           line-height: 2;"
            . "           border-radius: 0;"
            . "           min-width: 135px;"
            . "           outline: none;"
            . "       }"
            . "       .button{"
            . "           margin: 8px auto;"
            . "           border-radius: 24px;"
            . "       }"
            . "       .ok{"
            . "           color: #5eebcb;"
            . "           background-color: #fff;"
            . "           border-color: #5eebcb;"
            . "       }"
            . "       .info{"
            . "           color: #29c6e9;"
            . "           background-color: #fff;"
            . "           border-color: #29c6e9;"
            . "       }"
            . "   </style>"
            . "<head>"
            . "<body>"
            . "<div class=\"contents\">"
            . "   <h2 class=\"type\">"
            . "       $title"
            . "   </h2>"
            . "   <br />"
            . "   <a  href=$post class=\"btn ok button\">"
            . "       付款"
            . "   </a>"
            . "   <br />"
            . "   <a href=$quit_url   class=\"btn info button\">"
            . "       取消"
            . "   </a>"
            . "</div>"
            . "</body>"
            . "</html>"
            . "";
        echo $html;
    }

    public function returnTestSuccess($return_url, $cancel_url)
    {


        $url = url()->full();
        $str = strrchr($url, '?');
        if (settings('pay_scene') != 'test' || !$str) {
            abort(404);
        }
        $data = request()->all();

        $return_url_new = str_replace("~", "/", $return_url);
        $return_url_new = str_replace("@", "?", $return_url_new);
        $return_url     = str_replace("*", "#", $return_url_new);

        try {
            $this->pay->paySuccess($data);

            if ($return_url != "AAA") {
                $return_url .= "?result=success&out_trade_no=" . $data['out_trade_no'];

                return redirect($return_url);
            }
        } catch (\Exception $e) {
            \Log::info('模拟支付失败');
            \Log::info($e);
            if ($return_url != "AAA") {
                $return_url .= "?result=false&out_trade_no=" . $data['out_trade_no'];

                return redirect($return_url);
            }
        }
    }

}