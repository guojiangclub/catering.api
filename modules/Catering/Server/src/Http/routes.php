<?php

//银商异步通知
$router->post('union_notify/{type}', 'UnionPayNotifyController@notify');

$router->group(['prefix' => 'shitang'], function () use ($router) {
	$router->get('shop/info', 'UserController@shopInfo');

	$router->get('banners/list', 'BannersController@list');
	$router->get('homepage/popup', 'BannersController@popup');

	$router->post('oauth/MiniProgramLogin', 'MiniProgramLoginController@login')->name('api.oauth.miniprogram.login');
	$router->post('oauth/MiniProgramMobileLogin', 'MiniProgramLoginController@mobileLogin')->name('api.oauth.miniprogram.mobile.login');
	$router->get('new/user/gift', 'GiftNewUserController@index');

	$router->get('GettingUserProtocol', 'PublicController@GettingUserProtocol');
	$router->get('authorizationMobile', 'PublicController@authorizationMobile');
	$router->post('oauth/register', 'PublicController@register');
	$router->get('getPointGoods', 'PublicController@getPointGoods');

	$router->group(config('ibrand.shitang-api.routeAuthAttributes'), function ($router) {
		$router->get('me', 'UserController@me');
		$router->post('update/birthday', 'UserController@updateBirthday');

		$router->post('active/coupon/list', 'CouponController@index');
		$router->get('coupon/list', 'CouponController@list');
		$router->get('coupon/detail/{id}', 'CouponController@getDiscountDetail');
		$router->get('user/discounts/info', 'UserController@userDiscountsInfo')->name('api.user.userDiscountsInfo');
		$router->get('user/bindUserInfo', 'UserController@bindUserInfo')->name('api.user.bindUserInfo');
		$router->post('recharge', 'RechargeController@charge');
		$router->post('unifiedOrder', 'UnionPayController@unifiedOrder');
		$router->get('order/paidSuccess/{order_no}', 'UnionPayController@paidSuccess');
		$router->get('recharge/paidSuccess/{order_no}', 'RechargeController@paidSuccess');
		$router->get('order/cancel/{order_no}', 'UnionPayController@cancel');
		$router->get('order/list', 'OrderController@list');
		$router->get('getCouponConvert', 'CouponController@getCouponConvert');
		$router->get('activity/list', 'CouponCenterController@list');
	});

	$router->get('order/detail/{order_no}', 'OrderController@detail');

	//掌柜端
	$router->post('oauth/clerk', 'AuthClerkController@login')->name('api.shitang.oauth.clerk');
	$router->group(['prefix' => 'clerk', 'middleware' => ['st_clerk']], function () use ($router) {
		$router->get('payment/detail', 'CommercialController@detail');
		$router->get('balance/detail', 'CommercialController@balanceDetail');
		$router->get('order/list', 'CommercialController@orderList');
		$router->get('balance/order/list', 'CommercialController@balanceOrderList');
		$router->get('balance/used/order/list', 'CommercialController@balanceUsedOrderList');
		$router->get('order/detail/{order_no}', 'CommercialController@orderDetail');
		$router->get('balance/order/detail/{order_no}', 'CommercialController@balanceOrderDetail');
		$router->get('order/refund/{order_no}', 'CommercialController@refund');
		$router->get('coupon/usedAt/{code}', 'CommercialController@usedAt');
		$router->get('invalid/coupon/list', 'CommercialController@invalidCoupons');

		$router->group(['prefix' => 'user'], function () use ($router) {
			$router->get('list', 'CommercialUserController@list');
			$router->get('search', 'CommercialUserController@search');
			$router->get('detail/{user_id}', 'CommercialUserController@detail');
			$router->get('coupon/list/{user_id}', 'CommercialUserController@couponList');
			$router->get('point/list/{user_id}', 'CommercialUserController@pointList');
			$router->get('balance/list/{user_id}', 'CommercialUserController@balanceList');
			$router->get('order/list/{user_id}', 'CommercialUserController@orderList');
		});

		$router->group(['prefix' => 'point'], function () use ($router) {
			$router->get('opPointBase', 'PointController@opPointBase');
			$router->post('handlePoint', 'PointController@handlePoint');
		});
	});
});