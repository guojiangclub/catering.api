<?php

use Illuminate\Http\Request;

/*
 *  此分组下路由 需要通过 password-token 方式认证的 access token
 */

$router->post('oauth/sms', 'AuthController@smsLogin')->name('api.oauth.sms');
$router->post('oauth/token', 'AuthController@issueToken')->name('api.oauth.token');
$router->post('oauth/third-party', 'AuthController@openPlatform')->name('api.oauth.third-party');

$router->post('oauth/quicklogin', 'AuthController@quickLogin')->name('api.oauth.quickLogin');
$router->post('oauth/quickLoginEmptyMobile', 'AuthController@quickLoginEmptyMobile')->name('api.oauth.quickLoginEmptyMobile');

$router->post('oauth/employee/sms', 'StaffLoginController@login')->name('api.employee.oauth.sms');

$router->post('oauth/checkUnionID', 'AuthController@checkUnionID')->name('api.oauth.checkUnionID');
$router->post('oauth/UnionIDQuickLogin', 'AuthController@UnionIDQuickLogin')->name('api.oauth.UnionIDQuickLogin');

$router->post('oauth/MiniProgramLogin', 'MiniProgramLoginController@login')->name('api.oauth.miniprogram.login');
$router->post('oauth/MiniProgramUnionIdLogin', 'MiniProgramLoginController@unionIdLogin')->name('api.oauth.unionIdLogin');
$router->post('oauth/MiniProgramMobileLogin', 'MiniProgramLoginController@MiniProgramMobileLogin')->name('api.oauth.MiniProgramMobileLogin');

$router->get('testGid', 'MiniProgramLoginController@testGid');

$router->post('registration/nologin', 'RegistrationController@notLoggedActivateRegistration');

$router->get('ibrand', 'SystemSettingController@ibrand')->name('api.ibrand.content');
/************************* 首页 **********************/
$router->get('home/index', 'HomepageController@HomeIndex')->name('api.homepage.index');
$router->get('home/menu', 'HomepageController@HomeMenu')->name('api.homepage.menu');
$router->get('home/onlineService', 'HomepageController@onlineService')->name('api.homepage.onlineService');

$router->get('home/getHomeModulesData', 'HomepageController@getHomeModulesData')->name('api.homepage.getHomeModulesData');

//H5新首页
$router->get('home/HomeModulesData', 'HomepageController@HomeModulesData')->name('api.homepage.HomeModulesData');
//新人礼
$router->get('home/gift_new_user', 'HomepageController@giftNewUser')->name('api.homepage.gift.new.user');
//二维码
$router->get('home/QRCode', 'HomepageController@getQRCode')->name('api.homepage.get.QRCode');

//获取新的分类页面接口
$router->get('home/getHomeCategoryList', 'HomepageController@getHomeCategoryList')->name('api.homepage.get.getHomeCategoryList');
$router->get('home/getOpenSourceHomePage', 'HomepageController@openSourceHomePage')->name('api.homepage.get.openSourceHomePage');

$router->get('home/getHomeModulesData', 'HomepageController@getHomeModulesData')->name('api.homepage.getHomeModulesData');

$router->post('home/convertCode', 'HomepageController@convertCode')->name('api.homepage.convertCode');
$router->get('home/convertImg', 'HomepageController@draw2PxPoint')->name('api.homepage.draw2PxPoint');

$router->get('system/settings', 'SystemSettingController@index')->name('api.system.settings');
$router->get('system/init', 'SystemSettingController@init')->name('api.system.init');
$router->get('system/site', 'SystemSettingController@site')->name('api.system.site');
$router->get('system/analytics', 'SystemSettingController@analytics')->name('api.system.analytics');


$router->get('store/list', 'GoodsController@index')->name('api.store.list');
$router->get('store/detail/{id}', 'GoodsController@show')->name('api.store.detail');
$router->get('store/detail/{id}/stock', 'GoodsController@getStock')->name('api.store.detail.stock');
$router->get('store/detail/{id}/comments', 'GoodsController@getComments')->name('api.store.detail.comments');
$router->get('store/detail/{id}/discount', 'DiscountController@getDiscountByGoods')->name('api.store.detail.discount');
$router->get('store/category/list', 'CategoryController@index')->name('api.store.category.list');

$router->get('qrcode', 'QrCodeController@index')->name('api.qrCode');

$router->get('coupon/share/{id}', 'CouponController@getShareCoupon')->name('api.store.category.list');

$router->get('coupon/getInfo', 'CouponController@getCouponByType')->name('api.coupon.getInfo');

$router->get('discount/list', 'DiscountController@getDiscountList')->name('api.discount.list');
$router->get('discount/{id}/detail', 'DiscountController@getDiscountDetailByID')->name('api.discount.detail');

$router->get('wechat/jssdkconfig', 'WechatController@getJsConfig')->name('api.wechat.getJsConfig');

$router->get('users/balance/schemes', 'BalanceController@getSchemes')->name('api.user.balance.schemes');

$router->get('custom/page/{id}', 'CustomController@page')->name('api.costom.page');
//订单分享
$router->get('order/{order_no}/share', 'OrderController@shareOrder')->name('api.order.shareOrder.get');

$router->get('order/share/view/{roder_no}', 'OrderController@getShareOrderView')->name('api.order.shareOrder.view');

$router->get('coupon/share/agent/{agent_code}', 'CouponController@getAgentCouponView')->name('api.coupon.agent.share.coupon.view');


$router->group(config('dmp-api.routeAuthAttributes'), function ($router) {
    //新人礼
    $router->post('home/gift_new_user', 'HomepageController@giftNewUserLanded')->name('api.homepage.gift.new.user.landed');
    //生日礼
    $router->post('home/gift_birthday', 'HomepageController@giftBirthday')->name('api.homepage.gift.birthday');

    $router->get('store/goods/purchase/{goods_id}', 'GoodsController@goodsPurchase')->name('api.store.goods.purchase');
    $router->post('oauth/wechat', 'AuthController@bindWechat')->name('api.oauth.wechat');
    $router->post('oauth/employee/validate', 'StaffLoginController@validateStaff')->name('api.employee.oauth.validate');

    /************************* 用户相关路由 **********************/
    $router->get('/me', 'UserController@me')->name('api.me');
    $router->post('users/upload/avatar', 'UserController@uploadAvatar')->name('api.upload.avatar');
    $router->post('users/update/password', 'UserController@updatePassword')->name('api.update.password');
    $router->post('users/update/mobile', 'UserController@updateMobile')->name('api.update.mobile');
    $router->post('users/update/email', 'UserController@updateEmail')->name('api.update.email');
    $router->post('users/update/info', 'UserController@updateInfo')->name('api.update.info');

    $router->get('/users/group', 'UserController@getGroup')->name('api.user.group');

    $router->get('/users/ucenter', 'UserController@ucenter')->name('api.user.ucenter');
    $router->get('/users/market/ucenter', 'UserController@marketUserCenter')->name('api.user.market.ucenter');

    $router->get('card/me', 'CardController@myCard')->name('api.card.me.list');
    $router->get('card/me/barcode', 'CardController@myCardBarCode')->name('api.card.me.barcode');
    $router->post('card/me', 'CardController@store')->name('api.card.me');

    //$router->get('card/me/getWechatCardStatus',  'CardController@getWechatCardStatus')->name('api.card.me.getWechatCardStatus');
    //$router->post('card/me/activeWechatCart',  'CardController@activeWechatCart')->name('api.card.me.activeWechatCart');

    $router->get('users/point/list', 'UserController@pointList')->name('api.user.point.list');
    $router->get('users/point', 'WalletController@myPoint')->name('api.user.point');

    $router->post('users/BankAccount/add', 'UserController@addBankAccount')->name('api.user.bankAccount.add');
    $router->put('users/BankAccount/update/{id}', 'UserController@updateBankAccount')->name('api.user.bankAccount.update');
    $router->delete('users/BankAccount/delete/{id}', 'UserController@deleteBankAccount')->name('api.user.bankAccount.delete');
    $router->get('users/BankAccount/show', 'UserController@showBankAccountList')->name('api.user.bankAccount.show.list');
    $router->get('users/BankAccount/show/number', 'UserController@amountBankAccount')->name('api.user.bankAccount.show.number');
    $router->get('users/BankAccount/show-bank', 'UserController@showBankList')->name('api.user.bankAccount.show-bank');
    $router->get('users/BankAccount/show/{id}', 'UserController@showBankItem')->name('api.user.bankAccount.show');

    $router->get('users/BankAccount/deleteBankAccountByIds', 'UserController@deleteBankAccountByIds')->name('api.user.bankAccount.deleteBankAccountByIds');


    $router->post('users/dailySign', 'UserController@dailySign')->name('api.user.dailySign');
    $router->post('users/MiniProgramBindMobile', 'UserController@MiniProgramBindMobile')->name('api.user.MiniProgramBindMobile');

    $router->get('banks', 'BankController@index')->name('api.banks');

    $router->get('users/Balance/showList', 'UserController@balanceFluctuation')->name('api.user.Balance.showList');
    $router->get('users/Balance/showSum', 'UserController@balanceSum')->name('api.user.Balance.showSum');
    $router->post('users/Balance/recharge', 'UserController@balanceRecharge')->name('api.user.Balance.recharge');
    $router->post('users/Balance/consume', 'UserController@balanceConsume')->name('api.user.Balance.consume');

    $router->get('users/Balance/cash_list', 'BalanceController@getBalanceCashList')->name('api.user.Balance.getBalanceCashList');
    $router->post('users/Balance/apply_cash', 'BalanceController@applyBalanceCash')->name('api.user.Balance.applyBalanceCash');


    $router->post('users/balance/charge', 'BalanceController@charge')->name('api.user.balance.charge');
    $router->get('users/balance/paid', 'BalanceController@paid')->name('api.user.balance.paid');
    $router->get('users/balance/sum', 'BalanceController@sum')->name('api.user.balance.sum');
    $router->get('users/balance/list', 'BalanceController@index')->name('api.user.balance.list');

    $router->get('staff/shoppingLimit', 'UserController@staffShoppingLimit')->name('api.staff.shoppingLimit');

    $router->get('users/getUserAgentCode', 'UserController@getUserAgentCode')->name('api.user.getUserAgentCode');

    $router->get('user/bindUserMiniInfo', 'UserController@bindUserMiniInfo')->name('api.user.bindUserMiniInfo');

    /************************* 购物流程相关路由 **********************/
    $router->post('shopping/cart', 'ShoppingCartController@store')->name('api.shopping.cart.store');
    $router->get('shopping/cart', 'ShoppingCartController@index')->name('api.shopping.cart');
    $router->put('shopping/cart/{id}', 'ShoppingCartController@update')->name('api.shopping.cart.put');
    $router->delete('shopping/cart/{id}', 'ShoppingCartController@delete')->name('api.shopping.cart.delete');
    $router->post('shopping/cart/clear', 'ShoppingCartController@clear')->name('api.shopping.cart.clear');
    $router->get('shopping/cart/count', 'ShoppingCartController@count')->name('api.shopping.cart.count');

    $router->post('shopping/order/checkout', 'ShoppingController@checkout')->name('api.shopping.order.checkout');
    $router->post('shopping/order/checkout/point', 'ShoppingController@checkoutPoint')->name('api.shopping.order.checkout.point');
    $router->post('shopping/order/confirm', 'ShoppingController@confirm')->name('api.shopping.order.confirm');
    $router->post('shopping/order/confirm/point', 'ShoppingController@confirmPoint')->name('api.shopping.order.confirm');
    $router->post('shopping/order/charge', 'PaymentController@createCharge')->name('api.shopping.order.charge');
    $router->post('shopping/order/paid', 'PaymentController@paidSuccess')->name('api.shopping.order.paid');
    //$router->post('activity/order/paid', 'PaymentController@activityPaidSuccess')->name('api.activity.order.paid');
    $router->post('shopping/order/cancel', 'ShoppingController@cancel')->name('api.shopping.order.cancel');
    $router->post('shopping/order/received', 'ShoppingController@received')->name('api.shopping.order.received');
    $router->post('shopping/order/delete', 'ShoppingController@delete')->name('api.shopping.order.delete');
    $router->post('shopping/order/review', 'ShoppingController@review')->name('api.shopping.order.review');
    $router->get('shopping/order/extraInfo', 'ShoppingController@extraInfo')->name('api.shopping.order.extraInfo');

    $router->post('shopping/order/calculate', 'ShoppingController@calculateDiscount')->name('api.shopping.order.calculate');


    $router->get('store/list/{id}/coupon', 'GoodsController@getGoodsByCoupon')->name('api.store.list.coupon');

    $router->get('refund/base_info', 'RefundController@baseInfo')->name('api.refund.baseInfo');
    $router->post('refund/apply', 'RefundController@apply')->name('api.refund.apply');
    $router->get('refund/all', 'RefundController@index')->name('api.refund.all');
    $router->get('refund/show/{refund_no}', 'RefundController@show')->name('api.refund.show');
    $router->get('refund/list', 'RefundController@all')->name('api.refund.list');

    $router->post('refund/user/return', 'RefundController@returnRefund')->name('api.user.return');
    $router->post('refund/user/close', 'RefundController@close')->name('api.user.close');

    $router->get('coupon', 'CouponController@getCoupon')->name('api.coupon.list');
    $router->get('coupon/num', 'CouponController@getCouponNumByUser')->name('api.coupon.num');
    $router->get('coupon/{id}', 'CouponController@getCouponDetails')->where('id', '[0-9]+')->name('api.coupon');
    $router->post('coupon/convert/', 'CouponController@getCouponConvert')->name('api.coupon.convert');
    $router->post('coupon/code/convert/', 'CouponController@getCouponConvert')->name('api.coupon.code.convert');
    $router->get('coupon/bar', 'CouponController@getCouponBarCodes')->name('api.coupon.bar');

    $router->post('coupon/share/agent/image', 'CouponController@getAgentCouponImage')->name('api.coupon.agent.share.coupon.image');

    /************************ 优惠套餐相关路由 ************************/


    $router->get('suit/{id}/stock', 'SuitController@getStock')->name('api.suit.stock');


    /************************ 收货地址相关路由 ************************/

    $router->get('address', 'AddressController@getAddress')->name('api.address.list');
    $router->post('address/create', 'AddressController@createNew')->name('api.address.create');
    $router->put('address/update', 'AddressController@updateAddress')->name('api.address.update');
    $router->get('address/{id}', 'AddressController@getAddressDetails')->where('id', '[0-9]+')->name('api.address');
    $router->delete('address/{id}', 'AddressController@deleteAddress')->name('api.address.delete');
    $router->get('address/default', 'AddressController@getDefaultAddress')->name('api.address.default');


    /******************评论****************************/
    $router->get('comment/list', 'CommentsController@index')->name('api.comment.list');

    /*************************** 订单查询相关路由 *************************/

    $router->get('order/point/list', 'OrderController@getPointOrders')->name('api.order.point.list');

    $router->get('order/list', 'OrderController@getOrders')->name('api.order.list');
    $router->get('order/{order_no}', 'OrderController@getOrderDetails')->name('api.order');
    $router->get('order/{order_no}/refund/items', 'OrderController@getRefundItems')->name('api.order.refund.items');
    $router->post('order/status', 'OrderController@getOrdersByStatus')->name('api.order.status');
    $router->get('order/offline/{order_no}', 'OrderController@getOrderDetailsOffline')->name('api.order.offline');

    $router->get('order/refund/list', 'OrderController@getRefundOrders')->name('api.order.refund');

    $router->post('order/{order_no}/share', 'OrderController@shareOrder')->name('api.order.shareOrder.post');

    $router->get('order/share/img', 'OrderController@getShareOrderImg')->name('api.order.shareOrder.img');


    /*************************** 我的收藏相关路由 ********************/
    $router->get('favorite/', 'FavoriteController@getFav')->name('api.favorite');
    $router->post('favorite/store', 'FavoriteController@storeFav')->name('api.favorite.store');
    $router->post('favorite/delFavs', 'FavoriteController@delFavs')->name('api.favorite.delFavs');
    $router->get('favorite/isfav', 'FavoriteController@getIsFav')->name('api.favorite.isFav');

    /************************** 消息系统相关路由 ***************************/
    $router->get('messages', 'MessagesController@getMessagesByUser')->name('api.messages.list');
    $router->post('messages/create', 'MessagesController@createMessagesByUser')->name('api.messages.create');
    $router->put('messages/reply', 'MessagesController@updateMassges')->name('api.messages.reply');
    $router->get('messages/{id}', 'MessagesController@getMassgesById')->where('id', '[0-9]+')->name('api.messages');
    $router->get('messages/newcount', 'MessagesController@getNewThreadsCountByUser')->name('api.messages.newCount');


    /************************** 商品注册相关路由 ***************************/
    /*$router->get('registration','RegistrationController@getRegistrationByUser')->name('api.registration.list');
    $router->post('registration/validate','RegistrationController@validateRegistration')->name('api.registration.validate');
    $router->post('registration/activated','RegistrationController@activateRegistration')->name('api.registration.activated');
    $router->get('registration/goods/{id}','RegistrationController@getRegistrationGoods')->where('id','[0-9]+')->name('api.registration.goods');
    $router->get('registration/{id}','RegistrationController@getReceiveRegistrationGift')->where('id','[0-9]+')->name('api.registration');
    $router->get('registration/gift/{id}','RegistrationController@getRegistrationGiftList')->where('id','[0-9]+')->name('api.registration.gift');*/

    $router->get('registration', 'RegistrationController@getRegistrationByUser');
    $router->post('registration/validate', 'RegistrationController@validateRegistration');
    $router->post('registration/activated', 'RegistrationController@activateRegistration');
    $router->get('registration/goods/{id}', 'RegistrationController@getRegistrationGoods')->where('id', '[0-9]+');
    $router->get('registration/{id}', 'RegistrationController@getReceiveRegistrationGift')->where('id', '[0-9]+');
    $router->get('registration/gift/{id}', 'RegistrationController@getRegistrationGiftList')->where('id', '[0-9]+');
    $router->post('registration/receive/marketing/gift', 'RegistrationController@receiveMarketingGift')->where('id', '[0-9]+');


    /**************************** 发票相关的路由 **********************************/
    $router->post('invoice-user/add', 'InvoiceController@invoiceUserCreate')->name('api.invoice.add');
    $router->get('invoice-user/get', 'InvoiceController@getInvoiceUser')->name('api.invoice.user');
    $router->get('invoice/base-info', 'InvoiceController@getInvoiceConfig')->name('api.invoice.info');
    //   $router->post('invoice-order/add','InvoiceController@invoiceOrderCreate')->name('api.invoice.order.add');
    //   $router->post('invoice-order/update/{id}','InvoiceController@invoiceOrderUpdate')->name('api.invoice.order.update');
    //   $router->post('invoice-order/admin-edit/{id}','InvoiceController@invoiceOrderAdminEdit')->name('api.invoice.order.edit');


    $router->get('shipping/methods', 'ShippingController@getMethods')->name('api.shipping.methods');
    //到货提醒
    $router->post('store/goods/remind', 'GoodsController@goodsRemind')->name('api.store.goods.remind');
    //微信卡卷
    $router->get('member/wxcard/draw', 'MemberCardController@checkUserDraw')->name('api.member.wxcard.draw');
    $router->post('member/wxcard/activate', 'MemberCardController@wxCardActivate')->name('api.member.wxcard.activate');
    /*************************** 商品注册相关路由 *************************/
//
//    $router->group(['prefix' => 'registration'], function () use ($router){
//        $router->get('/', 'RegistrationController@index');
//        $router->get('create', 'RegistrationController@create');
//        $router->get('registered', 'RegistrationController@registered');
//        $router->get('binded', 'RegistrationController@binded');
//        $router->get('reg_list', 'RegistrationController@reg_list');
//        $router->post('activate','RegistrationController@activate');
//    });

    //签到
    $router->get('sign/getSignReward', 'UserSignController@getSignReward');
    $router->post('sign/doSign', 'UserSignController@doSign');
    $router->post('sign/doDraw', 'UserSignController@doDraw');
});

/************************* 支付相关路由 **********************/
$router->post('webhooks', 'PingxxPayNotifyController@webhooks')->name('api.webhooks');

/************************* 商品分类相关路由 **********************/
$router->get('category', 'CategoryController@index')->name('api.category.');
$router->get('category/group-categories', 'CategoryController@getCategroiesByGroupId')->name('api.category.group-categories');
$router->get('category/sub-categories', 'CategoryController@getSubCategroiesByNameOrId')->name('api.category.sub-categories');
$router->get('category/sub-category-ids', 'CategoryController@getSubIdsById')->name('api.category.sub-category-ids');
$router->get('category/ancestor/{id}', 'CategoryController@getAncestors')->name('api.category.ancestor');

/************************* 品牌相关路由 **********************/
$router->get('brand', 'BrandController@index')->name('api.brand.list');
$router->get('brand/{id}', 'BrandController@show')->name('api.brand');

/*************************** 推广位相关路由 ********************/
$router->get('banners', 'AdvertisementController@getCodeList')->name('api.banners.list');
$router->get('banners/{code}', 'AdvertisementController@getAdByCode')->name('api.banners');

/*************************** 秒杀活动相关路由 ********************/

$router->get('seckill/all', 'SeckillController@lists')->name('api.seckill.list');

/*************************** 拼团活动相关路由 **G******************/
$router->get('groupon/all', 'GrouponController@index')->name('api.groupon.list');

/************************** 小拼团 ******************************/
$router->get('multiGroupon/getGrouponUserList', 'MultGrouponController@getGrouponUserList')->name('api.multiGroupon.getGrouponUserList');
$router->get('multiGroupon/getGrouponItems', 'MultGrouponController@getGrouponItems')->name('api.multiGroupon.getGrouponItems');
$router->get('multiGroupon/showItem', 'MultGrouponController@showItem')->name('api.multiGroupon.showItem');
$router->get('multiGroupon/list', 'MultGrouponController@grouponList')->name('api.multiGroupon.grouponList');
$router->group(config('dmp-api.routeAuthAttributes'), function ($router) {
    $router->get('multiGroupon/createShareImage', 'MultGrouponController@createShareImage')->name('api.multiGroupon.createShareImage');
});


$router->group(['prefix' => 'location'], function () use ($router) {

    /*Route::get('/', function () {
        dd('This is the Location module index page.');
    });*/

    $router->get('city/{id?}', 'LocationController@shop')->name('location.shop.list')->where('id', '[0-9]+')->name('api.city');
    $router->get('city/list', 'LocationController@city')->name('location.city.list')->name('api.city.list');
    $router->get('search/shop', 'LocationController@search')->name('location.shop.search')->name('api.search.shop');

    $router->get('shop/hottest', 'LocationController@hottest')->name('location.shop.hottest')->name('api.shop.hottest');

});

$router->get('suit/{id}/list', 'SuitController@index')->name('api.suit.list.index');
$router->get('suit/list', 'SuitController@getSuitList')->name('api.suit.list.all');

//小程序获取openId
$router->get('wx_lite/open_id', 'AuthController@getOpenIdByCode');
//微信小程序登录
$router->post('mini/program/login', 'AuthController@MiniProgramLogin')->name('api.mini.program.login');

$router->get('oauth/getRedirectUrl', 'AuthController@getRedirectUrl');

/*$router->group(['middleware' => 'oauth'], function ($router) {

    // 发布内容单独设置频率限制
    $router->group([
        'middleware' => 'api.throttle',
        'limit' => config('api.rate_limits.publish.limits'),
        'expires' => config('api.rate_limits.publish.expires'),
    ], function ($router) {

        $router->get('order/getOrderInfo','ShoppingController@getOrderInfo');

    });

    // 请求内容单独设置频率限制
    $router->group([
        'middleware' => 'api.throttle',
        'limit' => config('api.rate_limits.access.limits'),
        'expires' => config('api.rate_limits.access.expires'),
    ], function ($router) {

        $router->get('me', 'UserController@me');
        $router->put('users/{id}', 'UserController@update');
        $router->post('users/update/password', 'UserController@updatePassword');
        $router->post('users/update/mobile', 'UserController@updateMobile');
        $router->post('users/update/email', 'UserController@updateEmail');
        $router->post('users/upload/avatar', 'UserController@uploadAvatar');
        $router->get('myPoint','WalletController@myPoint');
        $router->get('myPointLogs','WalletController@myPointLogs');
        $router->get('myCard','CardController@myCard');
        $router->get('myCardBr','CardController@myCardBr');

    });
});*/

/*
 * 此分组下路由 同时支持两种认证方式获取的 access_token
 */
/*$router->group([
    'middleware' => ['oauth', 'api.throttle'],
    'limit' => config('api.rate_limits.access.limits'),
    'expires' => config('api.rate_limits.access.expires'),
], function ($router) {

    $router->post('user/register', 'UserController@register');

    $router->get('users/{id}', 'UserController@show');

});*/


/*
 * 此分组下路由 为 TNF 专用路由，必须在 database 中设置 scopes 后才能生效
 */
/*$router->group([
    'middleware' => ['oauth:tnf', 'api.throttle'],
    'limit' => config('api.rate_limits.access.limits'),
    'expires' => config('api.rate_limits.access.expires'),
], function ($router) {

    $router->get('user/info', 'UserController@getUser');
    $router->post('user/update/{id}', 'UserController@updateUser');

});*/


//微信会员卡卷
$router->get('member/wxcard/qrcode', 'MemberCardController@cardQrCode')->name('api.member.wxcard.qrcode');
$router->get('member/wxcard/redirect', 'MemberCardController@activateRedirect')->name('api.member.wxcard.redirect');


$router->post('shoppingCart/discount', 'DiscountController@shoppingCartDiscount')->name('api.shopping.cart.discount');


//支付宝异步通知
$router->post('ali_notify', 'AliPayNotifyController@notify');
//支付宝同步通知
$router->get('ali_return/{return}', 'AliPayNotifyController@aliReturn');
//微信异步通知
$router->post('wechat_notify/{type}', 'WechatPayNotifyController@notify');


$router->post('shoppingCart/discount', 'DiscountController@shoppingCartDiscount')->name('api.shopping.cart.discount');

$router->post('wechat/group', 'WecahtShareGroupController@store')->name('api.wechat.group');
$router->group(config('dmp-api.routeAuthAttributes'), function ($router) {
	$router->get('group/list', 'WecahtShareGroupController@getList');

});

//游记
$router->group(['prefix' => 'travel', 'namespace'=>'Travel'],function($router){
	$router->get('contents/list', 'ContentsController@index');
	$router->get('tags/list', 'ContentsController@tags');
	$router->post('contents/comment', 'ContentsController@comment');
	$router->post('contents/praise', 'ContentsController@praise');
	$router->group(config('dmp-api.routeAuthAttributes'), function ($router) {
		$router->post('contents/publish', 'ContentsController@publish');
		$router->get('my/contents', 'ContentsController@getMyContents');
	});
	$router->get('content/detail/{id}', 'ContentsDetailController@detail');
	$router->get('content/comment/list/{id}', 'ContentsDetailController@getCommentsList');
	$router->get('content/praise/list/{id}', 'ContentsDetailController@getPraiseList');

	$router->get('share', 'ContentsDetailController@share');
	$router->get('share/template', 'ContentsDetailController@getTemplate');
});

$router->post('multiple/image/upload', 'UploadController@multipleImageUpload');

$router->post('multiple/image', 'UploadController@ImageUpload');

