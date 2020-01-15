<?php

namespace GuoJiangClub\Catering\Component\Payment\Charges;

use Carbon\Carbon;
use GuoJiangClub\Catering\Component\Payment\Contracts\PaymentChargeContract;
use GuoJiangClub\Catering\Component\Payment\Models\PaymentLog;
use Yansongda\Pay\Pay;

class DefaultCharge extends BaseCharge implements PaymentChargeContract
{

	public function createCharge($user_id, $channel, $type = 'order', $order_no, $amount, $subject, $body, $ip = '127.0.0.1', $openid = '', $extra = [], $submit_time = '')
	{
		// 模拟支付
		if (settings('pay_scene') == 'test') {
			return $this->createTestCharge($user_id, $channel, $type, $order_no, $amount, $subject, $body, $ip, $openid, $extra);
		}

		switch ($channel) {
			case 'wx_pub':
			case 'wx_pub_qr':
			case 'wx_lite':
				return $this->createWechatCharge($user_id, $channel, $type, $order_no, $amount, $subject, $body, $ip, $openid, $extra, $submit_time);

			case 'alipay_wap':
			case 'alipay_pc_direct':
				return $this->createAliCharge($user_id, $channel, $type, $order_no, $amount, $subject, $body, $ip, $openid, $extra, $submit_time);

			default:
				return null;
		}
	}

	protected function createTestCharge($user_id, $channel, $type = 'order', $order_no, $amount, $subject, $body, $ip = '127.0.0.1', $openid = '', $extra = [])
	{
		//模拟支付模式
		$quit_url   = 'AAA';
		$return_url = 'AAA';
		if (isset($extra['success_url']) And !empty($extra['success_url'])) {
			$return_url = str_replace("/", "~", $extra['success_url']);
			$return_url = str_replace("?", "@", $return_url);
			$return_url = str_replace("#", "*", $return_url);
		}
		if (isset($extra['cancel_url']) And !empty($extra['cancel_url'])) {
			$cancel_url = str_replace("/", "~", $extra['cancel_url']);
			$cancel_url = str_replace("?", "@", $cancel_url);
			$quit_url   = str_replace("#", "*", $cancel_url);
		}

		$test_url = route('test.pay.return.get', ['cancel_url' => $quit_url, 'success_url' => $return_url]);
		$test_url .= "?type=" . $type . "&channel=" . $channel . '&total_amount=' . ($amount / 100) . '&trade_no=test' . '&out_trade_no=' . $order_no . '&send_pay_date=' . Carbon::now();

		return [
			'type'      => $type,
			'order_no'  => $order_no,
			'channel'   => $channel,
			'pay_scene' => 'test',
			'url'       => $test_url,
		];
	}

	/**
	 * @param        $user_id
	 * @param        $channel
	 * @param string $type
	 * @param        $order_no
	 * @param        $amount
	 * @param        $subject
	 * @param        $body
	 * @param string $ip
	 * @param string $openid
	 * @param array  $extra
	 *
	 * @return array|null
	 */
	public function createWechatCharge($user_id, $channel, $type = 'order', $order_no, $amount, $subject, $body, $ip = '127.0.0.1', $openid = '', $extra = [], $submit_time)
	{
		$config = $this->getConfig('wechat');
		/*$delayTime = 120;*/
		$delayTime   = app('system_setting')->getSetting('order_auto_cancel_time') ? app('system_setting')->getSetting('order_auto_cancel_time') : 120;
		$time_expire = date('YmdHis', Carbon::now()->addMinute($delayTime)->timestamp);
		if ($submit_time) {
			$time_expire = date('YmdHis', Carbon::createFromTimeString($submit_time)->addMinute($delayTime)->timestamp);
		}

		$chargeData = [
			'body'             => mb_strcut($body, 0, 32, 'UTF-8'),
			'out_trade_no'     => $this->getWxPayCode($order_no, $channel),
			/*'out_trade_no' => $order_no,*/
			'total_fee'        => abs($amount),
			'spbill_create_ip' => $ip,
			'time_expire'      => $time_expire,
			'attach'           => json_encode(['user_id' => $user_id, 'order_sn' => $order_no, 'type' => $type, 'channel' => $channel], true),
		];

		switch ($channel) {
			case 'wx_pub_qr':
				$config['notify_url'] = $config['notify_url'] . '/wx';
				$pay                  = Pay::wechat($config)->scan($chargeData);
				$this->createPaymentLog('create_charge', Carbon::now(), $order_no, $chargeData['out_trade_no'], '', $amount, $channel, $type, 'SUCCESS', $user_id, $pay);

				return [
					'type'          => $type,
					'order_no'      => $order_no,
					'channel'       => $channel,
					'pay_scene'     => 'live',
					'amount'        => $chargeData['total_fee'],
					'credential'    => ['wx_pub_qr' => $pay['code_url']],//和pingxx保持一致的参数
					'metadata'      => ['order_sn' => $order_no, 'type' => $type],
					'amount_settle' => $chargeData['total_fee'],//和pingxx保持一致的参数
					'url'           => $pay['code_url'],
				];
			case 'wx_pub':
				$config['notify_url'] = $config['notify_url'] . '/wx';
				$chargeData['openid'] = $openid;
				$pay                  = Pay::wechat($config)->mp($chargeData);
				$this->createPaymentLog('create_charge', Carbon::now(), $order_no, $chargeData['out_trade_no'], '', $amount, $channel, $type, 'SUCCESS', $user_id, $pay);

				return [
					'type'      => $type,
					'order_no'  => $order_no,
					'channel'   => $channel,
					'pay_scene' => 'live',
					'wechat'    => $pay,
				];

			case 'wx_lite':
				$config = $this->getConfig('miniapp');
				//异步回调地址
				$config['notify_url'] = $config['notify_url'] . '/wx_lite';
				/*$user = request()->user();
			  $UserBind = UserBind::where('user_id', $user->id)->where('type', 'miniprogram')->where('app_id', $config['miniapp_id'])->first();
			   if (isset($UserBind->open_id)) {
				   $chargeData['openid'] = $UserBind->open_id;
			   }*/
				$chargeData['openid'] = $openid;
				$pay                  = Pay::wechat($config)->miniapp($chargeData);
				$this->createPaymentLog('create_charge', Carbon::now(), $order_no, $chargeData['out_trade_no'], '', $amount, $channel, $type, 'SUCCESS', $user_id, $pay);

				return [
					'type'      => $type,
					'order_no'  => $order_no,
					'channel'   => $channel,
					'pay_scene' => 'live',
					'wechat'    => $pay,
				];
			default:

				return null;
		}
	}

	public function createAliCharge($user_id, $channel, $type = 'order', $order_no, $amount, $subject, $body, $ip = '127.0.0.1', $openid = '', $extra = [], $submit_time = '')
	{

		$config = $this->getConfig('alipay');

		$delayTime   = app('system_setting')->getSetting('order_auto_cancel_time') ? app('system_setting')->getSetting('order_auto_cancel_time') : 1440;
		$time_expire = $delayTime . 'm';
		if ($submit_time AND ($gap = Carbon::now()->timestamp - strtotime($submit_time)) > 0) {
			$time_expire = ($delayTime - floor($gap / 60)) . 'm';
		}

		$extra = $this->createExtra($channel, '', $extra, $type);

		$amount = $amount / 100;

		$chargeData = [
			'body'            => mb_strcut($body, 0, 32, 'UTF-8'),
			'out_trade_no'    => $order_no,
			'total_amount'    => number_format($amount, 2, ".", ""),
			'subject'         => mb_strcut($subject, 0, 32, 'UTF-8'),
			'client_ip'       => $ip,
			'timeout_express' => $time_expire,
			'passback_params' => json_encode(['user_id' => $user_id, 'order_sn' => $order_no, 'type' => $type, 'channel' => $channel]),
		];

		if (!empty($extra['cancel_url'])) {
			$chargeData['quit_url'] = $extra['cancel_url'];
		}

		$return_url = $extra['success_url'] . $order_no;
		$return_url = str_replace("/", "~", $return_url);
		$return_url = str_replace("?", "@", $return_url);
		$return_url = str_replace("#", "*", $return_url);

		$config['return_url'] = $config['return_url'] . '/' . $return_url; //同步通知url
		$config['notify_url'] = $config['notify_url']; //异步通知url

		$ali_pay = [];
		if ($channel == 'alipay_pc_direct') {
			//unset($chargeData['passback_params']);
			$ali_pay = Pay::alipay($config)->web($chargeData);
			$key     = base64_encode($order_no);
			\Cache::put($order_no, html_entity_decode($ali_pay), 1);
			$this->createPaymentLog('create_charge', Carbon::now(), $order_no, $chargeData['out_trade_no'], '', $amount * 100, $channel, $type, 'SUCCESS', $user_id, $ali_pay);

			return [
				'type'      => $type,
				'order_no'  => $order_no,
				'channel'   => $channel,
				'pay_scene' => 'live',
				'key'       => $key,
			];
		}

		if ($channel == 'alipay_wap') {
			$ali_pay = Pay::alipay($config)->wap($chargeData);
			$this->createPaymentLog('create_charge', Carbon::now(), $order_no, $chargeData['out_trade_no'], '', $amount * 100, $channel, $type, 'SUCCESS', $user_id, $ali_pay);

			return [
				'type'      => $type,
				'order_no'  => $order_no,
				'channel'   => $channel,
				'pay_scene' => 'live',
				'form'      => html_entity_decode($ali_pay),
			];
		}

		return null;
	}

	public function getConfig($type, $channel = 'order')
	{
		$config = [];
		switch ($type) {
			case 'alipay':
				$config = config('pay.alipay');

				$config['app_id']     = settings('ibrand_alipay_app_id');
				$config['notify_url'] = url('api/ali_notify');
				$config['return_url'] = url('api/ali_return');

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
				$config['notify_url']  = url('api/wechat_notify');
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
				$config = config('pay.miniapp');
				if ($channel == 'activity') {
					$config['miniapp_id'] = settings('activity_mini_program_app_id');
					$config['mch_id']     = settings('activity_mini_program_pay_mch_id');
					$config['key']        = settings('activity_mini_program_pay_key');
				} else {
					$config['miniapp_id'] = settings('ibrand_miniapp_pay_miniapp_id');
					$config['mch_id']     = settings('ibrand_miniapp_pay_mch_id');
					$config['key']        = settings('ibrand_miniapp_pay_key');
				}

				$config['notify_url'] = url('api/wechat_notify');

				return $config;
				break;
			default:
				return [];
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
		/*$paymentLog = PaymentLog::where('order_no', $order_no)->where('action', 'result_pay')->get()->last();*/
		$paymentLog = PaymentLog::where('order_no', $order_no)->get()->last();
		if (!$paymentLog) {
			return [];
		}

		switch ($paymentLog->channel) {
			case 'wx_pub':
			case 'wx_pub_qr':
			case 'wx_lite':
				return $this->queryWechatByOutTradeNumber($paymentLog->transcation_order_no, $paymentLog->channel);

			case 'alipay_wap':
			case 'alipay_pc_direct':
				return $this->queryAliByOutTradeNumber($order_no, $paymentLog);

			default:
				return [];
		}
	}

	protected function queryWechatByOutTradeNumber($order_no, $channel)
	{
		$config = $this->getConfig('wechat');
		$para   = ['out_trade_no' => $order_no];

		if ($channel == 'wx_lite') {
			$config       = $this->getConfig('miniapp');
			$para['type'] = 'miniapp';
		}
		unset($config['notify_url']);

		$query = Pay::wechat($config);
		$data  = $query->find($para);

		if ($data['return_code'] == 'FAIL') {
			return [];
		}

		if ($data['result_code'] == 'FAIL') {
			return [];
		}

		if ($data['trade_state'] != 'SUCCESS') {
			return [];
		}

		$attach = json_decode($data['attach'], true);

		$charge['metadata']['order_sn'] = $attach['order_sn'];
		$charge['metadata']['type']     = $attach['type'];
		$charge['amount']               = $data['total_fee'];
		$charge['transaction_no']       = $data['transaction_id'];
		$charge['channel']              = $attach['channel'];
		$charge['id']                   = $data['out_trade_no'];
		$charge['time_paid']            = strtotime($data['time_end']);
		$charge['details']              = json_encode($data);

		$this->createPaymentLog('query_result_pay', Carbon::createFromTimestamp(strtotime($data['time_end'])), $attach['order_sn'], $data['out_trade_no'], $data['transaction_id'], $data['total_fee'], $attach['channel'], $attach['type'], 'SUCCESS', $attach['user_id'], $data);

		return $charge;
	}

	protected function queryAliByOutTradeNumber($order_no, $paymentLog)
	{
		$config = $this->getConfig('alipay');
		unset($config['notify_url']);
		unset($config['return_url']);
		$para = ['out_trade_no' => $order_no];

		$query = Pay::alipay($config);
		$data  = $query->find($para);

		if ($data['trade_status'] == "TRADE_SUCCESS" || $data['trade_status'] == "TRADE_FINISHED") {

			/*$attach = json_decode($data['passback_params'], true);*/

			$charge['metadata']['order_sn'] = $data['out_trade_no'];
			$charge['metadata']['type']     = $paymentLog->type;
			$charge['amount']               = $data['total_amount'] * 100;
			$charge['transaction_no']       = $data['trade_no'];
			$charge['channel']              = $paymentLog->channel;
			$charge['id']                   = $data['out_trade_no'];
			$charge['time_paid']            = strtotime($data['gmt_payment']);
			$charge['details']              = json_encode($data);

			$this->createPaymentLog('query_result_pay', $data['send_pay_date'], $data['out_trade_no'], $data['out_trade_no'], $data['trade_no'], $data['total_amount'] * 100, $paymentLog->channel, $paymentLog->type, 'SUCCESS', $paymentLog->user_id, $data);

			return $charge;
		}

		return [];
	}

}


