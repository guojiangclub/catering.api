<?php

namespace GuoJiangClub\Catering\Component\Payment\Charges;

abstract class BaseCharge
{

	private $name;

	public function __construct($name = 'default')
	{
		$this->name = $name;
	}

	/**
	 * @return mixed
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * 根据充值渠道生成extra参数
	 *
	 * @param $channel
	 *
	 * @return array
	 */
	protected function createExtra($channel, $openid = '', $extra = [], $type = 'order')
	{
		$result = [];

		switch ($channel) {
			case 'alipay_wap':
				if ($type == 'order') {
					$result = [
						'success_url' => isset($extra['success_url']) ? $extra['success_url'] : settings('wechat_pay_success_url'),
						'cancel_url'  => isset($extra['cancel_url']) ? $extra['cancel_url'] : settings('wechat_pay_fail_ucenter'),
					];
				} elseif ($type == 'recharge') {
					$result = [
						'success_url' => isset($extra['success_url']) ? $extra['success_url'] : settings('recharge_wechat_pay_success_url'),
						'cancel_url'  => isset($extra['cancel_url']) ? $extra['cancel_url'] : settings('recharge_wechat_pay_fail_url'),
					];
				} elseif ($type == 'activity') {
					$result = [
						'success_url' => isset($extra['success_url']) ? $extra['success_url'] : settings('wechat_activity_pay_success_url'),
						'cancel_url'  => isset($extra['cancel_url']) ? $extra['cancel_url'] : settings('wechat_activity_pay_fail_url'),
					];
				}

				break;
			case 'alipay_pc_direct':
				$result = [
					'success_url' => isset($extra['success_url']) ? $extra['success_url'] : config('pay.channel.order.alipay_pc_direct.success_url'),
				];
				break;
			case 'wx_pub_qr':
				$result = [
					'product_id' => isset($extra['product_id']) ? $extra['product_id'] : "tempProductId",
				];
				break;
			case 'wx_lite':
				$result = [
					'open_id' => $openid,
				];
				break;
			case 'wx_pub':
				$result = [
					'open_id' => $openid,
				];
				break;
		}

		return $result;
	}

	protected function getWxPayCode($order_sn, $channel)
	{
		switch ($channel) {
			case 'wx_pub':
			case 'wx_pub_qr':
			case 'wx_lite':
				return build_order_no('WXNO');
			default:
				return $order_sn;
		}
	}

}