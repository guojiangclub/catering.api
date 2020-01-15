<?php

return [

	'pingxx_live_secret_key' => env('PINGXX_LIVE_SECRET_KEY', 'sk_test_LW1WXHnf1mjHO4y548CajLyH'),

	'pingxx_app_id' => env('PINGXX_APP_ID', 'app_PqDqzHzTm9OGjXvH'),

	'channel' =>
		[
			'alipay_wap' => ['success_url' => 'http://dmp2016.chenhow.com/payment/ali_success', 'cancel_url' => 'http://dmp2016.chenhow.com/payment/alicancel'],
			'upacp_wap'  => ['result_url' => ''],
			'jdpay_wap'  => ['success_url' => '', 'fail_url' => ''],
		],

	'debug' => env('PAY_DEBUG', false),
];