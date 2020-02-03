<?php

/*
 * This file is part of ibrand/catering-backend.
 *
 * (c) iBrand <https://www.ibrand.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$router->group(['prefix' => 'admin/member'], function () use ($router) {
	$router->resource('users', 'UserController', ['except' => ['show'],
	                                              'names'  => [
		                                              'index'   => 'admin.users.index',
		                                              'create'  => 'admin.users.create',
		                                              'store'   => 'admin.users.store',
		                                              'edit'    => 'admin.users.edit',
		                                              'update'  => 'admin.users.update',
		                                              'destroy' => 'admin.users.destroy',
	                                              ],
	]);

	$router->get('users/banned', 'UserController@banned')->name('admin.users.banned');
	$router->post('users/getexport', 'UserController@getexport')->name('admin.users.getexport');
	$router->get('users/userexport', 'UserController@userexport')->name('admin.users.userexport');
	$router->get('users/download', 'UserController@download')->name('admin.users.download');

	$router->get('account/confirm/resend/{user_id}', 'UserController@resendConfirmationEmail')->name('admin.account.confirm.resend');

	$router->get('users/{id}/integrallist', 'UserController@integrallist')->name('admin.users.integrallist');
	$router->get('users/{id}/couponslist', 'UserController@couponslist')->name('admin.users.couponslist');
	$router->get('users/{id}/orderslist', 'UserController@orderslist')->name('admin.users.orderslist');

	$router->get('users/getUserPointData/{id}/{type}', 'UserController@getUserPointData')->name('admin.users.getUserPointList');

	$router->post('users/addPoint', 'UserController@addPoint')->name('admin.users.addPoint');

	$router->delete('users/{id}/everDelete', 'UserController@everDelete')->name('admin.users.everDelete');

	$router->get('users/getExportData', 'UserController@getExportData')->name('admin.users.getExportData');

	$router->get('users/edit/balance/list/{id}', 'BalanceController@getBalancePaginate')->name('admin.users.edit.balance.list');
	$router->post('users/edit/balance/add', 'BalanceController@operateBalance')->name('admin.users.edit.balance.operateBalance');

	$router->group(['prefix' => 'user/{id}', 'where' => ['id' => '[0-9]+']], function () use ($router) {
		$router->get('restore', 'UserController@restore')->name('admin.user.restore');
		$router->get('mark/{status}', 'UserController@mark')->name('admin.user.mark')->where(['status' => '[0,1,2]']);
		$router->get('password/change', 'UserController@changePassword')->name('admin.user.change-password');
		$router->post('password/change', 'UserController@updatePassword')->name('admin.user.change-password');
	});

	$router->get('users/importUser', 'UserController@importUser')->name('admin.users.importUser');
	$router->post('users/importUser/saveImport', 'UserController@saveImport')->name('admin.users.importUser.saveImport');
});

//分组
$router->group(['prefix' => 'admin/member/group'], function () use ($router) {
	$router->get('/', 'GroupController@index')->name('admin.users.group.list');
	$router->get('create', 'GroupController@create')->name('admin.users.group.create');
	$router->post('store', 'GroupController@store')->name('admin.users.group.store');
	$router->get('edit/{id}', 'GroupController@edit')->name('admin.users.group.edit');
	$router->post('delete/{id}', 'GroupController@delete')->name('admin.users.group.delete');
});

//会员
$router->group(['prefix' => 'admin/member/groups'], function () use ($router) {
	$router->get('grouplist', 'UserGroupController@grouplist')->name('admin.users.grouplist');
	$router->get('groupcreate', 'UserGroupController@groupcreate')->name('admin.users.groupcreate');
	$router->post('groupstore', 'UserGroupController@groupstore')->name('admin.users.groupstore');
	$router->post('groupchange/{id}', 'UserGroupController@groupupdate')->name('admin.users.groupchange ');
	$router->post('group/{id}/del', 'UserGroupController@deletedGroup')->name('admin.users.deletedGroup');
});

$router->resource('admin/member/rights', 'UserRightsController');

//数据统计
$router->group(['prefix' => 'admin/member/statistics'], function () use ($router) {
	$router->get('index', 'StatisticsController@index')->name('admin.statistics.index');
});

//会员积分
$router->group(['prefix' => 'admin/member/points'], function () use ($router) {
	$router->get('/', 'PointController@index')->name('admin.users.pointlist');

	$router->get('offline', 'PointController@pointOffline')->name('admin.member.points.offline');

	$router->get('default', 'PointController@pointDefault')->name('admin.member.points.default');

	$router->group(['prefix' => 'import'], function () use ($router) {
		$router->get('importPointModal', 'PointController@importPointModal')->name('admin.member.points.importPointModal');
		$router->get('getImportDataCount', 'PointController@getImportDataCount')->name('admin.member.points.getImportDataCount');
		$router->get('saveImportData', 'PointController@saveImportData')->name('admin.member.points.saveImportData');
	});
});

//会员余额
$router->group(['prefix' => 'admin/member/balances'], function () use ($router) {
	$router->get('/', 'BalanceController@index')->name('admin.users.balances.list');
	$router->get('importBalance/modal', 'BalanceController@importBalance')->name('admin.users.balance.importBalance');
	$router->post('importBalance/saveBalanceImport', 'BalanceController@saveBalanceImport')->name('admin.users.balance.saveBalanceImport');
});

$router->group(['prefix' => 'admin/member/entity'], function () use ($router) {
	$router->get('/', 'EntityCardController@index')->name('admin.users.entity.list');
	$router->get('getExportData', 'EntityCardController@getExportData')->name('admin.users.entity.getExportData');
	$router->get('zipFiles', 'EntityCardController@zipFiles')->name('admin.users.entity.zipFiles');
});

//储值管理
$router->group(['prefix' => 'admin/member/recharge'], function () use ($router) {
	$router->get('/', 'RechargeController@index')->name('admin.users.recharge.index');

	$router->get('/create', 'RechargeController@create')->name('admin.users.recharge.create');

	$router->post('/store', 'RechargeController@store')->name('admin.users.recharge.store');

	$router->get('/{id}/edit', 'RechargeController@edit')->name('admin.users.recharge.edit');

	$router->post('/{id}/update', 'RechargeController@update')->name('admin.users.recharge.update');

	$router->post('/{id}/delete', 'RechargeController@destroy')->name('admin.users.recharge.delete');

	$router->post('/toggleStatus', 'RechargeController@toggleStatus')->name('admin.users.recharge.toggleStatus');

	$router->get('/api/coupon', 'RechargeController@coupon_api')->name('admin.users.recharge.api.coupon');
});

$router->group(['prefix' => 'admin/member/log_recharge'], function () use ($router) {
	$router->get('/', 'RechargeController@log')->name('admin.users.recharge.log.index');
});

$router->group(['prefix' => 'admin/member/data'], function () use ($router) {
	$router->get('/', 'DataController@index')->name('admin.users.data.index');
	$router->get('getMonthData', 'DataController@getMonthData')->name('admin.users.data.getMonthData');
});

//订单
$router->group(['prefix' => 'admin/store/order'], function () use ($router) {
	//订单
	$router->get('/', 'OrdersController@index')->name('admin.orders.index');

	$router->get('detail/{id}', 'OrdersController@show')->name('admin.orders.show');

	$router->get('orderslist', 'OrdersController@orderslist')->name('admin.orders.orderlist');
	$router->post('orderslist', 'OrdersController@orderslist')->name('admin.orders.orderlist');
	$router->get('import/orders', 'OrdersController@ordersImport')->name('admin.orders.import');
	$router->post('import/order_send', 'OrdersController@importOrderSend')->name('admin.orders.saveimport');
	$router->get('deliver/{id}', 'OrdersController@ordersDeliver')->name('admin.orders.deliver');
	$router->get('deliver/{id}/edit', 'OrdersController@ordersDeliverEdit')->name('admin.orders.deliver.edit');
	$router->get('multiple_deliver', 'OrdersController@ordersMultipleDeliver')->name('admin.orders.multiple.deliver');

	$router->post('doDeliver', 'OrdersController@deliver')->name('admin.orders.savedeliver');
	$router->get('invoice/{id}', 'InvoiceController@edit')->name('admin.orders.invoice.edit');
	$router->post('invoice/update', 'InvoiceController@update')->name('admin.orders.invoice.update');
//  导出
	$router->post('export/excelExport', 'OrdersController@excelExport')->name('admin.orders.excelExport');
	$router->get('export/download/{url}', 'OrdersController@download')->name('admin.orders.download');

	$router->get('produce/edit/{id}', 'OrdersController@orderProduce')->name('admin.orders.produce.edit');
	$router->post('produce/update', 'OrdersController@orderProduceUpdate')->name('admin.orders.produce.update');

	$router->post('close/{id}', 'OrdersController@close')->name('admin.orders.close');

	$router->get('export/job', 'OrdersController@exportJob')->name('admin.orders.export.job');

	$router->get('export/getExportData', 'OrdersController@getExportData')->name('admin.orders.getExportData');

	$router->get('editAddress/{id}', 'OrdersController@editAddress')->name('admin.orders.editAddress');
	$router->post('postAddress', 'OrdersController@postAddress')->name('admin.orders.postAddress');
});

//积分商城
$router->group(['prefix' => 'admin/store/point-mall', 'namespace' => 'PointMall'], function () use ($router) {
	$router->group(['prefix' => 'goods'], function () use ($router) {
		$router->get('/', 'GoodsController@index')->name('admin.point-mall.goods.index');
		$router->get('create', 'GoodsController@create')->name('admin.point-mall.goods.create');
		$router->get('edit/{id}', 'GoodsController@edit')->name('admin.point-mall.goods.edit');
	});

	$router->group(['prefix' => 'orders'], function () use ($router) {
		$router->get('/', 'OrdersController@index')->name('admin.point-mall.orders.index');
		$router->get('show/{id}', 'OrdersController@show')->name('admin.point-mall.orders.show');
		$router->get('getExportData', 'OrdersController@getExportData')->name('admin.point-mall.orders.getExportData');
	});
});

$router->group(['prefix' => 'admin/store'], function () use ($router) {
	$router->post('upload/image', 'ImageController@postUpload')->name('upload.image');
	$router->post('upload/excel', 'ImageController@ExcelUpload')->name('upload.excel');
	$router->post('upload/uploadExcelFile', 'ImageController@uploadExcelFile')->name('upload.uploadExcelFile');

	//新的规格管理
	$router->group(['prefix' => 'specs'], function () use ($router) {

		$router->get('/', 'GoodsSpecController@index')->name('admin.goods.spec.index');
		$router->get('create', 'GoodsSpecController@create')->name('admin.goods.spec.create');
		$router->post('store', 'GoodsSpecController@store')->name('admin.goods.spec.store');
		$router->get('edit/{id}', 'GoodsSpecController@edit')->name('admin.goods.spec.edit');

		$router->get('specValue/{id}', 'GoodsSpecController@specValue')->name('admin.goods.spec.value.index');
		$router->post('getSpeValueData', 'GoodsSpecController@getSpeValueData')->name('admin.goods.spec.getSpeValueData');
		$router->post('specValue/store', 'GoodsSpecController@specValueStore')->name('admin.goods.spec.value.store');

		$router->get('editSpecValue', 'GoodsSpecController@editSpecValue')->name('admin.goods.spec.value.editSpecValue');
		$router->post('storeSpecValue', 'GoodsSpecController@storeSpecValue')->name('admin.goods.spec.value.storeSpecValue');
		$router->get('addSpecValue/{spec_id}', 'GoodsSpecController@addSpecValue')->name('admin.goods.spec.value.addSpecValue');

		$router->post('delSpecValue', 'GoodsSpecController@delSpecValue')->name('admin.goods.spec.value.delete');

		$router->post('delete/{id}', 'GoodsSpecController@destroy')->name('admin.goods.spec.delete');
	});

	//新模型管理
	$router->group(['prefix' => 'models'], function () use ($router) {

		$router->get('/', 'GoodsModelsController@index')->name('admin.goods.model.index');
		$router->get('create', 'GoodsModelsController@create')->name('admin.goods.model.create');
		$router->post('store', 'GoodsModelsController@store')->name('admin.goods.model.store');
		$router->get('edit/{id}', 'GoodsModelsController@edit')->name('admin.goods.model.edit');

		$router->post('delete/{id}', 'GoodsModelsController@delete')->name('admin.goods.model.delete');
		$router->post('deleteAttrValue/{id}', 'GoodsModelsController@deleteAttrValue')->name('admin.goods.model.deleteAttrValue');
		$router->post('deleteAttr/{id}', 'GoodsModelsController@deleteAttr')->name('admin.goods.model.deleteAttr');
		$router->post('checkSpec/{id}/{model_id}', 'GoodsModelsController@checkSpec')->name('admin.goods.model.checkSpec');
	});

	//公用属性管理
	$router->group(['prefix' => 'attribute'], function () use ($router) {

		$router->get('/', 'GoodsAttributeController@index')->name('admin.goods.attribute.index');
		$router->get('create', 'GoodsAttributeController@create')->name('admin.goods.attribute.create');
		$router->post('store', 'GoodsAttributeController@store')->name('admin.goods.attribute.store');
		$router->get('edit/{id}', 'GoodsAttributeController@edit')->name('admin.goods.attribute.edit');
//
		$router->post('delete/{id}', 'GoodsAttributeController@delete')->name('admin.goods.attribute.delete');
//        $router->post('deleteAttrValue/{id}', 'GoodsModelsController@deleteAttrValue')->name('admin.goods.model.deleteAttrValue');
//        $router->post('deleteAttr/{id}', 'GoodsModelsController@deleteAttr')->name('admin.goods.model.deleteAttr');
//        $router->post('checkSpec/{id}','GoodsModelsController@checkSpec')->name('admin.goods.model.checkSpec');

	});

	//新产品
	$router->group(['prefix' => 'goods'], function () use ($router) {
		$router->get('/', 'CommodityController@index')->name('admin.goods.index');
		$router->get('createBefore', 'CommodityController@createBefore')->name('admin.goods.createBefore');
		$router->get('create', 'CommodityController@create')->name('admin.goods.create');
		$router->get('edit/{id}', 'CommodityController@edit')->name('admin.goods.edit');
		$router->get('sort/update', 'CommodityController@updateSort')->name('admin.goods.sort.update');

		$router->get('excel', 'CommodityController@excel')->name('admin.goods.excel');

		$router->post('destroy/{id}', 'CommodityController@destroy')->name('admin.goods.destroy');
		$router->post('delete/{id}', 'CommodityController@delete')->name('admin.goods.delete');
		$router->post('restore/{id}', 'CommodityController@restore')->name('admin.goods.restore');

		$router->get('get_category', 'CommodityController@getCategoryByGroupID')->name('admin.goods.get_category');
		$router->get('uplode_inventorys', 'CommodityController@uplode_inventorys')->name('admin.goods.uplode_inventorys');

		$router->post('inventorys_insert', 'CommodityController@inventorys_insert')->name('admin.goods.inventorys_insert');

		$router->post('store', 'CommodityController@store')->name('admin.goods.store');
		$router->get('getAttribute', 'CommodityController@getAttribute')->name('admin.goods.getAttribute');
		$router->get('getSpecsData', 'CommodityController@getSpecsData')->name('admin.goods.getSpecsData');

		$router->get('getExportData', 'CommodityController@getExportData')->name('admin.goods.getExportData');

		$router->get('operationTitle', 'CommodityController@operationTitle')->name('admin.goods.operationTitle');
		$router->post('saveTitle', 'CommodityController@saveTitle')->name('admin.goods.saveTitle');

		$router->get('operationTags', 'CommodityController@operationTags')->name('admin.goods.operationTags');
		$router->post('saveTags', 'CommodityController@saveTags')->name('admin.goods.saveTags');

		$router->post('checkPromotionStatus', 'CommodityController@checkPromotionStatus')->name('admin.goods.checkPromotionStatus');
		$router->post('saveIsDel', 'CommodityController@saveIsDel')->name('admin.goods.saveIsDel');

		$router->group(['prefix' => 'import'], function () use ($router) {
			$router->get('/', 'GoodsImportController@index')->name('admin.goods.import');
			$router->get('importGoodsModal', 'GoodsImportController@importGoodsModal')->name('admin.goods.import.importGoodsModal');
			$router->get('getImportDataCount', 'GoodsImportController@getImportDataCount')->name('admin.goods.import.getImportDataCount');
			$router->get('saveImportData', 'GoodsImportController@saveImportData')->name('admin.goods.import.saveImportData');

			$router->group(['prefix' => 'bar_code'], function () use ($router) {
				$router->get('/', 'ProductsController@create')->name('admin.goods.barCode.import');
				$router->get('importBarCodeModal', 'ProductsController@importBarCodeModal')->name('admin.goods.barCode.importBarCodeModal');
				$router->get('getImportDataCount', 'ProductsController@getImportDataCount')->name('admin.goods.barCode.getImportDataCount');
				$router->get('saveImportData', 'ProductsController@saveImportData')->name('admin.goods.barCode.saveImportData');
			});
		});
	});

	//品牌
	$router->resource('brand', 'BrandController');

	//分类组
	$router->get('category_group', 'CategoryGroupController@index')->name('admin.cagetory.group.index');
	$router->get('category_group/create', 'CategoryGroupController@create')->name('admin.cagetory.group.create');
	$router->get('category_group/{id}/edit', 'CategoryGroupController@edit')->name('admin.cagetory.group.edit');

	$router->post('category_group/store', 'CategoryGroupController@store')->name('admin.cagetory.group.store');
	$router->post('category_group/{id}/destroy', 'CategoryGroupController@destroy')->name('admin.cagetory.group.destroy');

	//分类
	$router->get('category', 'CategoryController@index')->name('admin.category.index');
	$router->get('category/create', 'CategoryController@create')->name('admin.category.create');
	$router->post('category/store', 'CategoryController@store')->name('admin.category.store');
	$router->get('category/edit/{id}', 'CategoryController@edit')->name('admin.category.edit');
	$router->post('category/update/{id}', 'CategoryController@update')->name('admin.category.update');
	$router->get('category/check', 'CategoryController@check')->name('admin.category.check');
	$router->post('category/delete', 'CategoryController@destroy')->name('admin.category.delete');

	$router->get('category/category_sort', 'CategoryController@category_sort')->name('admin.category.category_sort');
	$router->get('category/goods_category/{type}', 'CategoryController@goods_category')->name('admin.category.goods_category');
});