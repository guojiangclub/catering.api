<?php

namespace GuoJiangClub\Catering\Component\Payment\Services;

use GuoJiangClub\Catering\Component\Payment\Models\PaymentRefundLog;
use Yansongda\Pay\Pay;

class RefundService
{
	public function createRefund($order_no, $transcation_order_no, $refund_no, $total, $amount, $channel, $description, $type = 'order', $extra = [])
	{
		switch ($channel) {
			case 'wx_pub':
			case 'wx_pub_qr':
			case 'wx_lite':
				return $this->createWechatRefund($refund_no, $transcation_order_no, $order_no, $total, $amount, $description, $channel, $type);

			case 'alipay_wap':
			case 'alipay_pc_direct':
				return $this->createAliRefund($refund_no, $order_no, $total, $amount, $description, $channel, $type);

			default:
				return null;
		}
	}

	/**
	 * @param $refund_no      :退款编号
	 * @param $transaction_id :订单支付的时候微信返回的交易单号
	 * @param $total          :订单总金额
	 * @param $amount         :退款金额
	 * @param $description    :退款说明
	 *
	 * @return \Yansongda\Supports\Collection
	 */
	public function createWechatRefund($refund_no, $transcation_order_no, $order_no, $total, $amount, $description, $channel, $type)
	{
		$config     = $this->getConfig('wechat');
		$refundData = [
			'out_trade_no'  => $transcation_order_no,
			'out_refund_no' => $refund_no,
			'total_fee'     => abs($total),
			'refund_fee'    => abs($amount),
			'refund_desc'   => $description,
		];
		if ($channel == 'wx_lite') {
			$config             = $this->getConfig('miniapp');
			$refundData['type'] = 'miniapp';
		}

		$result = Pay::wechat($config)->refund($refundData);

		if ($result['return_code'] == 'FAIL') {
			return [];
		}

		if ($result['result_code'] == 'FAIL') {
			return [];
		}

		$refund = [
			'order_no'  => $order_no,
			'refund_no' => $refund_no,
			'refund_id' => $result['refund_id'],
			'total'     => $total,
			'amount'    => $result['refund_fee'],
			'channel'   => $channel,
			'type'      => $type,
			'meta'      => $result,
		];

		return $refund;
	}

	/**
	 *
	 * @param $order_no    :订单支付时传入的商户订单号
	 * @param $amount      :需要退款的金额
	 * @param $description :退款的原因说明
	 *
	 * @return \Yansongda\Supports\Collection
	 */
	public function createAliRefund($refund_no, $order_no, $total, $amount, $description, $channel, $type)
	{
		$config = $this->getConfig('alipay');
		unset($config['notify_url']);
		$refundData = [
			'out_trade_no'  => $order_no,
			'refund_amount' => number_format($amount / 100, 2, ".", ""),
			'refund_reason' => $description,
		];
		$result     = Pay::alipay($config)->refund($refundData);

		$refund = [
			'order_no'  => $order_no,
			'refund_no' => $refund_no,
			'refund_id' => $result['trade_no'],
			'total'     => $total,
			'amount'    => $result['refund_fee'] * 100,
			'channel'   => $channel,
			'type'      => $type,
			'meta'      => $result,
		];

		return $refund;
	}

	public function createPaymentRefundLog($action, $operate_time, $refund_no, $order_no, $refund_id, $amount, $channel, $type = 'order', $status, $meta = [])
	{
		PaymentRefundLog::create([
			'action'       => $action,
			'operate_time' => $operate_time,
			'refund_no'    => $refund_no,
			'order_no'     => $order_no,
			'refund_id'    => $refund_id,
			'amount'       => $amount,
			'channel'      => $channel,
			'type'         => $type,
			'status'       => $status,
			'meta'         => json_encode($meta),
		]);
	}

	public function getConfig($type)
	{
		$config = [];
		switch ($type) {
			case 'alipay':
				$config = config('pay.alipay');

				$config['app_id'] = settings('ibrand_alipay_app_id');

				$aliPublicKey = settings('ibrand_alipay_ali_public_key');

				$aliPublicKey             = str_replace("-----BEGIN PUBLIC KEY-----", "", $aliPublicKey);
				$aliPublicKey             = str_replace("-----END PUBLIC KEY-----", "", $aliPublicKey);
				$aliPublicKey             = str_replace("\n", "", $aliPublicKey);
				$config['ali_public_key'] = $aliPublicKey;

				if (file_exists(storage_path('share') . '/ibrand_alipay_private_key.pem')) {
					$private_content       = file_get_contents(storage_path('share') . '/ibrand_alipay_private_key.pem');
					$private_content       = str_replace("-----BEGIN RSA PRIVATE KEY-----", "", $private_content);
					$private_content       = str_replace("-----END RSA PRIVATE KEY-----", "", $private_content);
					$private_content       = str_replace("\n", "", $private_content);
					$config['private_key'] = $private_content;
				} else {
					$config['private_key'] = '';
				}

				return $config;
				break;
			case 'wechat':
				$config                = config('pay.wechat');
				$config['app_id']      = settings('ibrand_wechat_pay_app_id');
				$config['mch_id']      = settings('ibrand_wechat_pay_mch_id');
				$config['key']         = !empty($config['key']) ? $config['key'] : settings('ibrand_wechat_pay_key');
				$config['cert_client'] = storage_path('share') . '/ibrand_wechat_pay_apiclient_cert.pem';
				$config['cert_key']    = storage_path('share') . '/ibrand_wechat_pay_apiclient_key.pem';
				$config['log']         = [ // optional
				                           'file'  => storage_path('logs') . '/pay-wechat.log',
				                           'level' => 'debug',
				                           'mode'  => 'dev',
				];

				return $config;
				break;
			case 'miniapp':
				$config                = config('pay.miniapp');
				$config['miniapp_id']  = settings('ibrand_miniapp_pay_miniapp_id');
				$config['mch_id']      = settings('ibrand_miniapp_pay_mch_id');
				$config['key']         = settings('ibrand_miniapp_pay_key');
				$config['cert_client'] = storage_path('share') . '/ibrand_mini_pay_apiclient_cert.pem';
				$config['cert_key']    = storage_path('share') . '/ibrand_mini_pay_apiclient_key.pem';

				return $config;
				break;
			default:
				return [];
		}
	}
}