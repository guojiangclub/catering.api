<?php

$router->group(['prefix' => 'shitang'], function ($router) {
	//优惠券
	$router->group(['prefix' => 'coupon'], function ($router) {
		$router->get('/', 'CouponController@index')->name('admin.shitang.coupon.index');
		$router->get('create', 'CouponController@create')->name('admin.shitang.coupon.create');
		$router->get('edit/{id}', 'CouponController@edit')->name('admin.shitang.coupon.edit');
		$router->post('store', 'CouponController@store')->name('admin.shitang.coupon.store');
		$router->get('useRecord', 'CouponController@useRecord')->name('admin.shitang.coupon.useRecord');
		$router->get('show', 'CouponController@showCoupons')->name('admin.shitang.coupon.show');
		$router->get('sendCoupon', 'CouponController@sendCoupon')->name('admin.shitang.coupon.sendCoupon');
		$router->get('filterUser', 'CouponController@filterUser')->name('admin.shitang.coupon.sendCoupon.filterUser');
		$router->post('getUsers', 'CouponController@getUsers')->name('admin.shitang.coupon.getUsers');
		$router->post('getSelectedUsersByID', 'CouponController@getSelectedUsersByID')->name('admin.shitang.coupon.getSelectedUsersByID');
		$router->post('sendAction', 'CouponController@sendAction')->name('admin.shitang.coupon.sendAction');
		$router->get('couponCode', 'CouponController@couponCode')->name('admin.shitang.coupon.couponCode');
		$router->post('createCouponCode', 'CouponController@createCouponCode')->name('admin.shitang.coupon.createCouponCode');
		$router->get('getExportData', 'CouponController@getExportData')->name('admin.shitang.coupon.getExportData');
		$router->get('getCouponsUsedExportData', 'CouponController@getCouponsUsedExportData')->name('admin.shitang.coupon.getCouponsUsedExportData');
		$router->get('getCouponsExportData', 'CouponController@getCouponsExportData')->name('admin.shitang.coupon.getCouponsExportData');

		$router->get('getShop', 'PublicController@getShop')->name('admin.shitang.getShop');
		$router->get('getMarket', 'PublicController@getMarket')->name('admin.shitang.getMarket');
		$router->post('getShopData', 'PublicController@getShopData')->name('admin.shitang.getShopData');
		$router->post('getMarketData', 'PublicController@getMarketData')->name('admin.shitang.getMarketData');

		$router->get('list', 'CouponController@getCouponsList')->name('admin.shitang.coupon.list.modal');
	});

	//新人进店礼
	$router->group(['prefix' => 'new_user/gift'], function ($router) {
		$router->get('/', 'GiftNewUserController@index')->name('admin.shitang.gift.new.user');
		$router->get('/create', 'GiftNewUserController@create')->name('admin.shitang.gift.new.user.create');
		$router->post('/store', 'GiftNewUserController@store')->name('admin.shitang.gift.new.user.store');
		$router->get('/edit/{id}', 'GiftNewUserController@edit')->name('admin.shitang.gift.new.user.edit');
		$router->get('/delete/{id}', 'GiftNewUserController@delete')->name('admin.shitang.gift.new.user.delete');
		$router->post('/toggleStatus', 'GiftNewUserController@toggleStatus')->name('admin.shitang.gift.new.user.toggleStatus');
	});

	//生日礼
	$router->group(['prefix' => 'birthday/gift'], function ($router) {
		$router->get('/', 'GiftBirthdayController@index')->name('admin.shitang.gift.birthday');
		$router->get('create', 'GiftBirthdayController@create')->name('admin.shitang.gift.birthday.create');
		$router->post('store', 'GiftBirthdayController@store')->name('admin.shitang.gift.birthday.store');
		$router->get('edit/{id}', 'GiftBirthdayController@edit')->name('admin.shitang.gift.birthday.edit');
		$router->get('delete/{id}', 'GiftBirthdayController@delete')->name('admin.shitang.gift.birthday.delete');
		$router->post('toggleStatus', 'GiftBirthdayController@toggleStatus')->name('admin.shitang.gift.birthday.toggleStatus');
	});

	// 定向发券
	$router->group(['prefix' => 'directional/coupon'], function ($router) {
		$router->get('/', 'DirectionalCouponController@index')->name('admin.shitang.directional.coupon.index');
		$router->get('/create', 'DirectionalCouponController@create')->name('admin.shitang.directional.coupon.create');
		$router->post('/store', 'DirectionalCouponController@store')->name('admin.shitang.directional.coupon.store');
		$router->get('/api/coupon', 'DirectionalCouponController@coupon_api')->name('admin.shitang.directional.coupon.api.coupon');
		$router->post('/searchUser', 'DirectionalCouponController@searchUser')->name('admin.shitang.directional.searchUser');
		$router->post('/delete', 'DirectionalCouponController@destroy')->name('admin.shitang.directional.delete');
		$router->get('/{id}/edit', 'DirectionalCouponController@edit')->name('admin.shitang.directional.edit');
		$router->get('/{id}/log', 'DirectionalCouponController@log')->name('admin.shitang.directional.log');
	});

	$router->group(['prefix' => 'share/setting'], function ($router) {
		$router->get('/', 'SettingController@shareSetting')->name('admin.shitang.gift.shareSetting');
	});

	//领劵中心
	$router->group(['prefix' => 'gift/center'], function ($router) {
		$router->get('coupon/list', 'CouponCenterController@getCouponsList')->name('admin.shitang.gift.center.coupon.list');
		$router->get('/', 'CouponCenterController@index')->name('admin.shitang.gift.center.index');
		$router->get('create', 'CouponCenterController@create')->name('admin.shitang.gift.center.create');
		$router->get('edit/{id}', 'CouponCenterController@edit')->name('admin.shitang.gift.center.edit');
		$router->post('store', 'CouponCenterController@store')->name('admin.shitang.gift.center.store');
	});

	$router->get('pay/settings', 'SettingController@index');
	$router->get('pay/qr_code', 'SettingController@payQrCode')->name('admin.shitang.pay.qrcode');
	$router->post('setting/save', 'SettingController@saveSettings')->name('admin.shitang.setting.save');
	$router->get('auto/send', 'AutoSendController@index');
});

//客来店
$router->group(['prefix' => 'customer/shop'], function ($router) {
	$router->get('settings', 'SettingController@base')->name('admin.shitang.customer.shop.settings');
	$router->get('message', 'WechatMessageSettingController@index')->name('admin.shitang.customer.shop.wechat');
	$router->get('setting/wechat/order/remind', 'WechatMessageSettingController@orderRemind')->name('admin.shitang.customer.shop.wechat.order.remind');
	$router->get('setting/wechat/goods/deliver', 'WechatMessageSettingController@goodsDeliver')->name('admin.shitang.customer.shop.wechat.goods.deliver');
	$router->get('setting/wechat/arrival/goods', 'WechatMessageSettingController@goodsArrival')->name('admin.shitang.customer.shop.wechat.goods.arrival');
	$router->get('setting/wechat/sales/service', 'WechatMessageSettingController@salesService')->name('admin.shitang.customer.shop.wechat.sales.service');
	$router->get('setting/wechat/goods/refund', 'WechatMessageSettingController@goodsRefund')->name('admin.shitang.customer.shop.wechat.goods.refund');
	$router->get('setting/wechat/customer/paid', 'WechatMessageSettingController@customerPaid')->name('admin.shitang.customer.shop.wechat.customer.paid');
	$router->get('setting/wechat/money/changed', 'WechatMessageSettingController@moneyChanged')->name('admin.shitang.customer.shop.wechat.money.changed');
	$router->get('setting/wechat/point/changed', 'WechatMessageSettingController@pointChanged')->name('admin.shitang.customer.shop.wechat.point.changed');
	$router->get('setting/wechat/charge/success', 'WechatMessageSettingController@chargeSuccess')->name('admin.shitang.customer.shop.wechat.charge.success');
	$router->get('setting/wechat/member/grade', 'WechatMessageSettingController@memberGrade')->name('admin.shitang.customer.shop.wechat.member.grade');
	$router->get('setting/wechat/sales/notice', 'WechatMessageSettingController@salesNotice')->name('admin.shitang.customer.shop.wechat.sales.notice');
	$router->get('setting/wechat/refund/result', 'WechatMessageSettingController@refundResult')->name('admin.shitang.customer.shop.wechat.refund.result');
	$router->get('setting/wechat/groupon/grouponSuccess', 'WechatMessageSettingController@grouponSuccess')->name('admin.shitang.customer.shop.wechat.groupon.success');
	$router->get('setting/wechat/groupon/grouponFailed', 'WechatMessageSettingController@grouponFailed')->name('admin.shitang.customer.shop.wechat.groupon.failed');
	$router->get('setting/wechat/activity/notice', 'WechatMessageSettingController@activityNotice')->name('admin.shitang.customer.shop.wechat.activity.notice');
	$router->get('setting/wechat/activity/notice/gift', 'WechatMessageSettingController@activityNoticeGift')->name('admin.shitang.customer.shop.wechat.activity.notice.gift');
	$router->get('setting/wechat/point/stPointChange', 'WechatMessageSettingController@stPointChange')->name('admin.shitang.customer.shop.wechat.stPointChange');
	$router->get('setting/wechat/stBalanceChange', 'WechatMessageSettingController@stBalanceChange')->name('admin.shitang.customer.shop.wechat.stBalanceChange');
	$router->get('setting/wechat/stCouponChange', 'WechatMessageSettingController@stCouponChange')->name('admin.shitang.customer.shop.wechat.stCouponChange');
	$router->get('setting/wechat/joinSuccess', 'WechatMessageSettingController@joinSuccess')->name('admin.shitang.customer.shop.wechat.joinSuccess');
	$router->get('setting/wechat/paidSuccess', 'WechatMessageSettingController@paidSuccess')->name('admin.shitang.customer.shop.wechat.paidSuccess');
	$router->get('setting/wechat/statisticsResult', 'WechatMessageSettingController@statisticsResult')->name('admin.shitang.customer.shop.wechat.statisticsResult');
	$router->get('setting/wechat/couponOverdueRemind', 'WechatMessageSettingController@couponOverdueRemind')->name('admin.shitang.customer.shop.wechat.couponOverdueRemind');
	$router->post('setting/wechat/save', 'WechatMessageSettingController@save')->name('admin.shitang.customer.shop.wechat.save');


	$router->resource('banner', 'BannerController');
	$router->resource('activity', 'ActivityController')->middleware('st.init');
	//订单管理
	$router->group(['prefix' => 'orders'], function ($router) {
		$router->get('/', 'OrdersController@index')->name('admin.shitang.orders.index');
		$router->get('detail/{id}', 'OrdersController@show')->name('admin.shitang.orders.show');
		$router->get('orderslist', 'OrdersController@orderslist')->name('admin.shitang.orders.orderlist');
		$router->post('orderslist', 'OrdersController@orderslist')->name('admin.shitang.orders.orderlist');
		$router->get('import/orders', 'OrdersController@ordersImport')->name('admin.shitang.orders.import');
		$router->post('import/order_send', 'OrdersController@importOrderSend')->name('admin.shitang.orders.saveimport');
		$router->get('deliver/{id}', 'OrdersController@ordersDeliver')->name('admin.shitang.orders.deliver');
		$router->get('deliver/{id}/edit', 'OrdersController@ordersDeliverEdit')->name('admin.shitang.orders.deliver.edit');
		$router->get('multiple_deliver', 'OrdersController@ordersMultipleDeliver')->name('admin.shitang.orders.multiple.deliver');

		$router->post('doDeliver', 'OrdersController@deliver')->name('admin.shitang.orders.savedeliver');
		$router->get('invoice/{id}', 'InvoiceController@edit')->name('admin.shitang.orders.invoice.edit');
		$router->post('invoice/update', 'InvoiceController@update')->name('admin.shitang.orders.invoice.update');
		//导出
		$router->post('export/excelExport', 'OrdersController@excelExport')->name('admin.shitang.orders.excelExport');
		$router->get('export/download/{url}', 'OrdersController@download')->name('admin.shitang.orders.download');

		$router->get('produce/edit/{id}', 'OrdersController@orderProduce')->name('admin.shitang.orders.produce.edit');
		$router->post('produce/update', 'OrdersController@orderProduceUpdate')->name('admin.shitang.orders.produce.update');

		$router->post('close/{id}', 'OrdersController@close')->name('admin.shitang.orders.close');

		$router->get('export/job', 'OrdersController@exportJob')->name('admin.shitang.orders.export.job');

		$router->get('export/getExportData', 'OrdersController@getExportData')->name('admin.shitang.orders.getExportData');

		$router->get('editAddress/{id}', 'OrdersController@editAddress')->name('admin.shitang.orders.editAddress');
		$router->post('postAddress', 'OrdersController@postAddress')->name('admin.shitang.orders.postAddress');
	});

	$router->group(['prefix' => 'clerk'], function ($router) {
		$router->get('/', 'ClerkController@index')->name('admin.shitang.shop.clerk');
		$router->get('create', 'ClerkController@create')->name('admin.shitang.shop.clerk.create');
		$router->get('edit/{clerk_id}', 'ClerkController@edit')->name('admin.shitang.shop.clerk.edit');
		$router->get('status', 'ClerkController@toggleStatus')->name('admin.shitang.shop.clerk.status');
		$router->post('store', 'ClerkController@store')->name('admin.shitang.shop.clerk.store');
		$router->post('update/{clerk_id}', 'ClerkController@update')->name('admin.shitang.shop.clerk.update');
		$router->get('delete', 'ClerkController@delete')->name('admin.shitang.shop.clerk.delete');
		$router->post('bind/weChat', 'ClerkController@bindWeChat')->name('admin.shitang.shop.clerk.bind.wechat');
	});
});
