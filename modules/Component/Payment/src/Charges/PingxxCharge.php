<?php

namespace GuoJiangClub\Catering\Component\Payment\Charges;

use Carbon\Carbon;
use GuoJiangClub\Catering\Component\Payment\Contracts\PaymentChargeContract;
use Pingpp\Charge;
use Pingpp\Pingpp;

class PingxxCharge extends BaseCharge implements PaymentChargeContract
{
	/**
	 * 创建charge数据，与pingpp集成对接
	 *
	 * @param        $user_id
	 * @param        $channel
	 * @param string $type
	 * @param        $order_no
	 * @param        $amount
	 * @param        $subject
	 * @param        $body
	 * @param string $ip
	 *
	 * @return Charge
	 */
	public function createCharge($user_id, $channel, $type = 'order', $order_no, $amount, $subject, $body, $ip = '127.0.0.1', $openid = '', $extra = [], $submit_time = '')
	{
		Pingpp::setApiKey($this->getApiKey());
		$this->setPrivateKey();

		$extra = $this->createExtra($channel, $openid, $extra, $type);

		$delayTime = app('system_setting')->getSetting('order_auto_cancel_time') ? app('system_setting')->getSetting('order_auto_cancel_time') : 1440;

		/*if (in_array($channel, ['wx_pub', 'wx_pub_qr'])) {
			$delayTime = 120;
		}*/
		$time_expire = Carbon::now()->addMinute($delayTime)->timestamp;
		if ($submit_time) {
			$time_expire = Carbon::createFromTimeString($submit_time)->addMinutes($delayTime)->timestamp;
		}

		$chargeData = [
			'app'         => ['id' => $this->getPingAppId()],
			'channel'     => $channel,
			'currency'    => 'cny',
			'amount'      => $amount, //因为pingpp 是以分为单位
			'client_ip'   => $ip,
			'order_no'    => $this->getWxPayCode($order_no, $channel),
			'subject'     => mb_strcut($subject, 0, 32, 'UTF-8'),
			'body'        => mb_strcut($body, 0, 32, 'UTF-8'),
			'extra'       => $extra,
			'metadata'    => ['user_id' => $user_id, 'order_sn' => $order_no, 'type' => $type],
			'time_expire' => $time_expire,
		];

		$charge = Charge::create($chargeData);

		/*if ($type == 'recharge') {  //写入充值记录
			$this->rechargeRepository->create(['user_id' => $user_id,
				'recharge_no' => $order_no,
				'amount' => $amount,
				'payment_type' => $channel
			]);
		}*/

		return $charge;
	}

	private function getPingAppId()
	{
		if ($appId = settings('pingxx_app_id')) {
			return $appId;
		}

		return config('payment.pingxx_app_id');
	}

	private function getApiKey()
	{
		if (settings('pingxx_pay_scene') AND settings('pingxx_pay_scene') == 'live' AND $apiKey = settings('pingxx_live_secret_key')) {
			return $apiKey;
		}

		if ($apiKey = settings('pingxx_test_secret_key')) {
			return $apiKey;
		}

		return config('payment.pingxx_live_secret_key');
	}

	private function setPrivateKey()
	{
		/*if ($privateKey = settings('pingxx_rsa_private_key')) {
			Pingpp::setPrivateKey($privateKey);
		} else {*/
		Pingpp::setPrivateKeyPath(storage_path('share') . '/rsa_private_key.pem');
		/*}*/
	}

	public function createPaymentLog($action, $operate_time, $order_no, $transcation_order_no, $transcation_no, $amount, $channel, $type = 'order', $status, $user_id, $meta = [])
	{
		// TODO: Implement createPaymentLog() method.
	}

	public function queryByOutTradeNumber($order_no)
	{
		return [];
	}
}