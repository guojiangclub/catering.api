<?php

//银商异步通知
$router->post('union_notify/{type}', 'UnionPayNotifyController@notify');

$router->get('system/init', 'SystemSettingController@init')->name('api.system.init');
$router->get('users/balance/schemes', 'BalanceController@getSchemes')->name('api.user.balance.schemes');

$router->get('store/list', 'GoodsController@index')->name('api.store.list');
$router->get('store/detail/{id}', 'GoodsController@show')->name('api.store.detail');
$router->get('store/detail/{id}/stock', 'GoodsController@getStock')->name('api.store.detail.stock');
$router->get('store/detail/{id}/comments', 'GoodsController@getComments')->name('api.store.detail.comments');
$router->get('store/detail/{id}/discount', 'DiscountController@getDiscountByGoods')->name('api.store.detail.discount');
$router->get('store/category/list', 'CategoryController@index')->name('api.store.category.list');

$router->group(config('ibrand.shitang-api.routeAuthAttributes'), function($router){
    $router->get('users/balance/sum', 'BalanceController@sum')->name('api.user.balance.sum');
    $router->get('users/balance/list', 'BalanceController@index')->name('api.user.balance.list');

    $router->get('users/point/list', 'UserController@pointList')->name('api.user.point.list');
    $router->get('users/point', 'WalletController@myPoint')->name('api.user.point');

    $router->get('store/goods/purchase/{goods_id}', 'GoodsController@goodsPurchase')->name('api.store.goods.purchase');

    /************************ 收货地址相关路由 ************************/

    $router->get('address', 'AddressController@getAddress')->name('api.address.list');
    $router->post('address/create', 'AddressController@createNew')->name('api.address.create');
    $router->put('address/update', 'AddressController@updateAddress')->name('api.address.update');
    $router->get('address/{id}', 'AddressController@getAddressDetails')->where('id', '[0-9]+')->name('api.address');
    $router->delete('address/{id}', 'AddressController@deleteAddress')->name('api.address.delete');
    $router->get('address/default', 'AddressController@getDefaultAddress')->name('api.address.default');

    /*************************** 我的收藏相关路由 ********************/
    $router->get('favorite/', 'FavoriteController@getFav')->name('api.favorite');
    $router->post('favorite/store', 'FavoriteController@storeFav')->name('api.favorite.store');
    $router->post('favorite/delFavs', 'FavoriteController@delFavs')->name('api.favorite.delFavs');
    $router->get('favorite/isfav', 'FavoriteController@getIsFav')->name('api.favorite.isFav');

    $router->post('shopping/order/checkout/point', 'ShoppingController@checkoutPoint')->name('api.shopping.order.checkout.point');
    $router->post('shopping/order/confirm/point', 'ShoppingController@confirmPoint')->name('api.shopping.order.confirm');
    $router->post('shopping/order/cancel', 'ShoppingController@cancel')->name('api.shopping.order.cancel');
    $router->post('shopping/order/received', 'ShoppingController@received')->name('api.shopping.order.received');
    $router->post('shopping/order/delete', 'ShoppingController@delete')->name('api.shopping.order.delete');
    $router->post('shopping/order/review', 'ShoppingController@review')->name('api.shopping.order.review');
    $router->get('shopping/order/extraInfo', 'ShoppingController@extraInfo')->name('api.shopping.order.extraInfo');
    $router->get('order/point/list', 'OrderController@getPointOrders')->name('api.order.point.list');
});

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