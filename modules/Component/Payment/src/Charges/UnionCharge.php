<?php

namespace GuoJiangClub\Catering\Component\Payment\Charges;

use Carbon\Carbon;
use GuoJiangClub\Catering\Component\Payment\Contracts\PaymentChargeContract;
use GuoJiangClub\Catering\Component\Payment\Models\PaymentLog;
use GuoJiangClub\Catering\Server\Service\UnionPayService;

class UnionCharge extends BaseCharge implements PaymentChargeContract
{
    public function createCharge($user_id, $channel, $type = 'order', $order_no, $amount, $subject, $body, $ip = '127.0.0.1', $openid = '', $extra = [], $submit_time = '')
    {
        switch ($channel) {
            case 'wx_lite':
                return $this->createUnionCharge($user_id, $channel, $type, $order_no, $amount, $openid);

            default:
                return null;
        }
    }

    /**
     * @param        $user_id
     * @param        $channel
     * @param string $type
     * @param        $order_no
     * @param        $amount
     * @param string $openid
     *
     * @return array|null
     */
    public function createUnionCharge($user_id, $channel, $type = 'order', $order_no, $amount, $openid = '')
    {
        $config     = settings('shitang_miniProgram_pay_config');
        $chargeData = [
            'attachedData'     => [
                'type'    => $type,
                'channel' => $channel,
            ],
            'msgSrc'           => $config['msgSrc'],
            'msgType'          => 'wx.unifiedOrder',
            'requestTimestamp' => date('Y-m-d H:i:s'),
            'merOrderId'       => $order_no,
            'mid'              => $config['mid'],
            'tid'              => $config['tid'],
            'instMid'          => $config['instMid'],
            'totalAmount'      => $amount,
            'notifyUrl'        => $config['notifyUrl'],
            'signType'         => 'MD5',
            'subOpenId'        => $openid,
            'tradeType'        => 'MINI',
        ];

        switch ($channel) {
            case 'wx_lite':
                $result = UnionPayService::miniProgramUnifiedOrder($chargeData, $config);
                if (!empty($result)) {
                    $this->createPaymentLog('create_charge', Carbon::now(), $order_no, '', '', $amount, $channel, $type, 'SUCCESS', $user_id, $result);

                    return ['miniPayRequest' => $result['miniPayRequest'], 'merOrderId' => $result['merOrderId']];
                }

                return [];
                break;
            default:

                return null;
        }
    }

    public function createPaymentLog($action, $operate_time, $order_no, $transcation_order_no, $transcation_no, $amount, $channel, $type = 'order', $status, $user_id, $meta = [])
    {
        PaymentLog::create([
            'action'               => $action,
            'operate_time'         => $operate_time,
            'order_no'             => $order_no,
            'transcation_order_no' => $transcation_order_no,
            'transcation_no'       => $transcation_no,
            'amount'               => $amount,
            'channel'              => $channel,
            'type'                 => $type,
            'status'               => $status,
            'user_id'              => $user_id,
            'meta'                 => json_encode($meta),
        ]);
    }

    public function queryByOutTradeNumber($order_no)
    {
        $config = settings('shitang_miniProgram_pay_config');

        $queryData = [
            'msgSrc'           => $config['msgSrc'],
            'msgType'          => 'query',
            'requestTimestamp' => date('Y-m-d H:i:s'),
            'mid'              => $config['mid'],
            'tid'              => $config['tid'],
            'instMid'          => $config['instMid'],
            'merOrderId'       => $order_no,
        ];

        $result = UnionPayService::orderQuery($queryData, $config);
        if ($result) {
            $result['attachedData'] = json_decode($result['attachedData'], true);

            return $result;
        }

        return false;
    }
}