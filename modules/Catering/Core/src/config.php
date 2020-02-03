<?php

return [
	'database' => [
		'prefix' => 'ca_',
	],

	'routeAttributes' => [
		'middleware' => ['api', 'cors'],
	],

	'routeAuthAttributes' => [
		'middleware' => ['auth:api'],
	],
	'mini_program'        => [
		'shitang' => [
			'app_id'  => env('SHITANG_MINI_PROGRAM_APPID', ''),
			'secret'  => env('SHITANG_MINI_PROGRAM_SECRET', ''),
			'token'   => '',
			'aes_key' => '',
		],
	],
	'clerk_rule'          => [
		'balance'      => [
			'clerk_balance_recharge'    => '充值',
			'clerk_balance_consumption' => '购物消费',
			'clerk_balance_other'       => '其他消费',
		],
		'balance_note' => [
			'clerk_balance_recharge'    => '店员代操作充值;',
			'clerk_balance_consumption' => '店员代操作消费;',
			'clerk_balance_other'       => '店员代操作其他;',
		],
		'point'        => [
			'clerk_point_update' => '添加积分',
		],
		'point_note'   => [
			'clerk_point_update' => '店员操作添加积分',
		],
	],
];