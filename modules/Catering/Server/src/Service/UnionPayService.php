<?php

namespace GuoJiangClub\Catering\Server\Service;

use GuzzleHttp\Client;

class UnionPayService
{
	public static $client = null;

	public static $miniProgramPayConfig = [];

	public static $officialPayConfig = [];

	public static function init()
	{
		if (is_null(self::$client) || !self::$client instanceof Client) {
			self::$client = new Client();
		}

		return self::$client;
	}

	/**
	 * 小程序支付参数
	 *
	 * @return array
	 */
	public static function getMiniProgramConfig(): array
	{
		if (empty(self::$miniProgramPayConfig)) {
			self::$miniProgramPayConfig = settings('shitang_miniProgram_pay_config');
		}

		return self::$miniProgramPayConfig;
	}

	/**
	 * h5 公众号支付参数
	 *
	 * @return array
	 */
	public static function getOfficialConfig(): array
	{
		if (empty(self::$officialPayConfig)) {
			self::$officialPayConfig = settings('market_official_pay_config');
		}

		return self::$officialPayConfig;
	}

	/**
	 * 小程序支付下单
	 *
	 * @param $order
	 * @param $openId
	 * @param $sub_mid
	 *
	 * @return bool|mixed
	 */
	public static function miniProgramUnifiedOrder($params, $config)
	{
		$params['sign'] = self::MakeSign($params, $config['signKey']);
		$options        = [
			'body'    => json_encode($params),
			'headers' => ['content-type' => 'application/json'],
		];

		$cli      = self::init();
		$response = $cli->post($config['api_url'], $options);
		$contents = $response->getBody()->getContents();
		$result   = json_decode($contents, true);
		if (empty($result) || !isset($result['miniPayRequest']) || $result['errCode'] != 'SUCCESS') {
			\Log::info($contents);

			return false;
		}

		return $result;
	}

	/**
	 * 支付宝h5下单
	 *
	 * @param $order
	 * @param $sub_mid
	 *
	 * @return string
	 */
	public static function officialUnifiedOrder($order, $sub_mid)
	{
		$config = self::getOfficialConfig();

		$params = [
			'msgSrc'           => $config['msgSrc'],
			'msgType'          => 'trade.jsPay',
			'requestTimestamp' => date('Y-m-d H:i:s'),
			'merOrderId'       => $order->order_no,
			'mid'              => $config['mid'],
			'tid'              => $config['tid'],
			'instMid'          => $config['instMid'],
			'totalAmount'      => $order->amount,
			'notifyUrl'        => $config['notifyUrl'],
			'returnUrl'        => $config['returnUrl'] . '?order_no=' . $order->order_no . '&type=ali_pay',
			'signType'         => 'MD5',
			'divisionFlag'     => true,
			'platformAmount'   => 0,
			'subOrders'        => [
				[
					'mid'         => $sub_mid,
					'totalAmount' => $order->amount,
				],
			],
		];

		$params['sign'] = self::MakeSign($params, $config['signKey']);
		$buff           = "";
		foreach ($params as $k => $v) {
			if ($v !== "" && !is_array($v) && !in_array($k, ['requestTimestamp', 'notifyUrl', 'returnUrl', 'divisionFlag'])) {
				$buff .= $k . "=" . $v . "&";
			} elseif ($k == "divisionFlag") {
				$buff .= $k . "=true&";
			} elseif ($k == "requestTimestamp" || $k == "notifyUrl" || $k == "returnUrl") {
				$buff .= $k . "=" . urlencode($v) . "&";
			} elseif ($v !== "" && is_array($v) && !empty($v) && !in_array($k, ['requestTimestamp', 'notifyUrl', 'returnUrl', 'divisionFlag'])) {
				$buff .= $k . "=" . urlencode(json_encode($v)) . "&";
			} else {
				continue;
			}
		}

		$buff = trim($buff, "&");

		return $config['api_url'] . '?' . $buff;
	}

	/**
	 * 查询订单
	 *
	 * @param        $order
	 * @param string $type
	 *
	 * @return array|bool
	 */
	public static function orderQuery($params, $config)
	{
		$params['sign'] = self::MakeSign($params, $config['signKey']);
		$options        = [
			'body'    => json_encode($params),
			'headers' => ['content-type' => 'application/json'],
		];

		$cli      = self::init();
		$response = $cli->post($config['api_url'], $options);
		$contents = $response->getBody()->getContents();
		$result   = json_decode($contents, true);
		if (empty($result) || $result['errCode'] != 'SUCCESS' || $result['merOrderId'] != $params['merOrderId']) {
			\Log::info($contents);

			return false;
		}

		return $result;
	}

	/**
	 * 订单退款
	 *
	 * @param        $order
	 * @param        $sub_mid
	 * @param string $type
	 *
	 * @return bool|mixed
	 */
	public static function orderRefund($order_no, $refundAmount)
	{
		$config = self::getMiniProgramConfig();

		$params = [
			'msgSrc'           => $config['msgSrc'],
			'msgType'          => 'refund',
			'requestTimestamp' => date('Y-m-d H:i:s'),
			'mid'              => $config['mid'],
			'tid'              => $config['tid'],
			'instMid'          => $config['instMid'],
			'merOrderId'       => $order_no,
			'refundAmount'     => $refundAmount,
		];

		$params['sign'] = self::MakeSign($params, $config['signKey']);
		$options        = [
			'body'    => json_encode($params),
			'headers' => ['content-type' => 'application/json'],
		];

		$cli      = self::init();
		$response = $cli->post($config['api_url'], $options);
		$contents = $response->getBody()->getContents();
		$result   = json_decode($contents, true);
		if (empty($result) || !isset($result['errCode']) || !isset($result['status']) || $result['errCode'] != 'SUCCESS' || $result['status'] != 'TRADE_SUCCESS') {
			\Log::info($contents);

			return false;
		}

		return $result;
	}

	/**
	 * 生成签名
	 *
	 * @param array $config
	 *
	 * @return string
	 */
	public static function MakeSign(array $config, $signKey, $type = 'MD5')
	{
		//签名步骤一：按字典序排序参数
		ksort($config);
		$string = self::ToUrlParams($config);
		//签名步骤二：在string后加入KEY
		$string = $string . $signKey;
		//签名步骤三：MD5加密或者HMAC-SHA256
		if ($type == "MD5") {
			$string = md5($string);
		} elseif ($type == "SHA256") {
			$string = hash_hmac("sha256", $string, $signKey);
		} else {
			return false;
		}

		//签名步骤四：所有字符转为大写
		$result = strtoupper($string);

		return $result;
	}

	/**
	 * 格式化参数格式化成url参数
	 *
	 * @param array $config
	 *
	 * @return string
	 */
	public static function ToUrlParams(array $config)
	{
		$buff = "";
		foreach ($config as $k => $v) {
			if ($k != "sign" && $k != "divisionFlag" && $v !== "" && !is_array($v)) {
				$buff .= $k . "=" . $v . "&";
			} elseif ($k == "divisionFlag") {
				$buff .= $k . "=true&";
			} elseif ($k != "sign" && $k != "divisionFlag" && $v !== "" && is_array($v) && !empty($v)) {
				$buff .= $k . "=" . json_encode($v) . "&";
			} else {
				continue;
			}
		}

		$buff = trim($buff, "&");

		return $buff;
	}

	/**
	 * 产生随机字符串，不长于32位
	 *
	 * @param int $length
	 *
	 * @return string
	 */
	public static function getNonceStr($length = 32)
	{
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";
		$str   = "";
		for ($i = 0; $i < $length; $i++) {
			$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}

		return $str;
	}
}