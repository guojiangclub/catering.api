<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-03-21
 * Time: 2:29
 */

namespace ElementVip\Server\Http\Controllers;

use ElementVip\Component\Point\Repository\PointRepository;
use ElementVip\Component\Order\Processor\OrderProcessor;
use ElementVip\Component\Order\Repositories\OrderRepository;
use ElementVip\Component\Payment\Services\PaymentService;
use ElementVip\Component\Payment\Services\ActivityPaymentService;
use Illuminate\Events\Dispatcher;
use ElementVip\Activity\Core\Repository\MemberRepository;
use ElementVip\Component\Balance\Repository\BalanceRepository;
use ElementVip\Activity\Core\Repository\PaymentRepository;

class PingxxPayNotifyController extends Controller
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

    public function __construct(PaymentService $paymentService
        , OrderRepository $orderRepository
        , OrderProcessor $orderProcessor
        , PointRepository $pointRepository
        , Dispatcher $events
        , BalanceRepository $balanceRepository
        , ActivityPaymentService $activityPaymentService
        , MemberRepository $memberRepository
        , PaymentRepository $paymentRepository
    )
    {
        $this->payment           = $paymentService;
        $this->orderRepository   = $orderRepository;
        $this->orderProcessor    = $orderProcessor;
        $this->pointRepository   = $pointRepository;
        $this->events            = $events;
        $this->balanceRepository = $balanceRepository;
        $this->activityPayment   = $activityPaymentService;
        $this->member            = $memberRepository;
        $this->paymentRepository = $paymentRepository;
    }

    public function webhooks()
    {
        $raw_data = file_get_contents('php://input');

        $result = $this->verifyPing($raw_data);

        if ($result === 1) {
            $event = json_decode($raw_data, true);

            if ($event['type'] == 'charge.succeeded') {

                $charge = $event['data']['object'];

                if (isset($charge['metadata']['type']) && $charge['metadata']['type'] == 'activity' && $charge['paid']) {  //如果支付成功
                    $this->activityPayment->paySuccess($charge);
                } else {
                    $this->payment->paySuccess($charge);
                }

                http_response_code(200); // PHP 5.4 or greater
                exit;
            } elseif ($event['type'] == 'refund.succeeded') {
                $refund = $event['data']['object'];
                // ...
                http_response_code(200); // PHP 5.4 or greater
                exit;
            } else {
                /**
                 * 其它类型 ...
                 * - summary.daily.available
                 * - summary.weekly.available
                 * - summary.monthly.available
                 * - transfer.succeeded
                 * - red_envelope.sent
                 * - red_envelope.received
                 * ...
                 */
                //http_response_code(200);

                // 异常时返回非 2xx 的返回码
                http_response_code(400);
                exit;
            }
        } else {
            return $this->verifyPingFail($result);
        }
    }

    protected function verifyPing($rawData)
    {
        $headers          = \Pingpp\Util\Util::getRequestHeaders();
        $signature        = isset($headers['X-Pingplusplus-Signature']) ? $headers['X-Pingplusplus-Signature'] : null;
        $pulicKeyContents = $this->getPublicKey();

        return openssl_verify($rawData, base64_decode($signature), $pulicKeyContents, 'sha256');
    }

    protected function verifyPingFail($result)
    {
        if ($result === 0) {
            http_response_code(400);
            echo 'verification failed';
            exit;
        }
        http_response_code(400);
        echo 'verification error:' . $result;
        exit;
    }

    private function getPublicKey()
    {
        if ($publicKey = settings('pingxx_rsa_public_key')) {
            return $publicKey;
        } else {
            return file_get_contents(public_path() . '/pingpp_rsa_public_key.pem');
        }
    }

}