<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016-09-08
 * Time: 21:21
 */

namespace ElementVip\Server\Http\Controllers;

use Carbon\Carbon;
use Cart;
use Dingo\Api\Http\Response;
use ElementVip\Component\Address\Models\Address;
use ElementVip\Component\User\Models\User;
use ElementVip\Component\Discount\Applicators\DiscountApplicator;
use ElementVip\Component\Discount\Models\Coupon;
use ElementVip\Component\Discount\Models\Discount;
use ElementVip\Component\Discount\Services\DiscountService;
use ElementVip\Component\Bundle\Repository\BundleRepository;
use ElementVip\Component\Bundle\Applicator\BundleApplicator;
use ElementVip\Component\Point\Repository\PointRepository;
use ElementVip\Component\Point\Applicator\PointApplicator;
use ElementVip\Component\Invoice\Model\InvoiceOrder;
use ElementVip\Component\Invoice\Model\InvoiceUser;
use ElementVip\Component\Order\Models\Comment;
use GuoJiangClub\Catering\Component\Order\Models\Order;
use GuoJiangClub\Catering\Component\Order\Models\OrderItem;
use ElementVip\Component\Order\Processor\OrderProcessor;
use ElementVip\Component\Order\Repositories\Eloquent\OrderRepositoryEloquent;
use ElementVip\Component\Order\Repositories\OrderRepository;
use ElementVip\Component\Product\Models\Goods;
use ElementVip\Component\Product\Models\Product;
use ElementVip\Component\Product\Repositories\GoodsRepository;
use ElementVip\Component\Product\Repositories\ProductRepository;
use ElementVip\Component\Discount\Repositories\CouponRepository;
use ElementVip\Component\Refund\Models\Refund;
use ElementVip\Member\Backend\Models\Staff;
use ElementVip\Notifications\PointChanged;
use ElementVip\Notifications\PointRecord;
use ElementVip\Server\Transformers\CouponsTransformer;
use ElementVip\Store\Backend\Model\SingleDiscountCondition;
use iBrand\Component\MultiGroupon\Service\MultiGrouponService;
use iBrand\FreeEvent\Core\Services\FreeService;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Collection;
use Illuminate\Events\Dispatcher;
//use Milon\Barcode\DNS1D;
use DNS1D;
use DB;
use ElementVip\Component\Suit\Repositories\SuitRepository;
use ElementVip\Component\Suit\Services\SuitService;
use  iBrand\Shoppingcart\Item;
use ElementVip\Store\Backend\Repositories\GoodsUserLimitRepository;
use ElementVip\Store\Backend\Repositories\GoodsLimitRepository;
use ElementVip\Component\Product\Models\SpecsValue;
use ElementVip\Component\Seckill\Repositories\SeckillItemRepository;
use ElementVip\Component\Seckill\Services\SeckillService;
use iBrand\Component\Groupon\Repositories\GrouponItemRepository;
use iBrand\Component\Groupon\Services\GrouponService;
use ElementVip\Component\Order\Models\Adjustment;
use ElementVip\Component\Order\Models\SpecialType;


class ShoppingController extends Controller
{
    private $goodsRepository;
    private $productRepository;
    private $discountService;
    private $orderRepository;
    private $discountApplicator;
    private $couponRepository;
    private $orderProcessor;
    private $bundleRepository;
    private $bundleApplicator;
    private $pointRepository;
    private $pointApplicator;
    private $event;
    private $suitRepository;
    private $suitService;
    private $goodsUserLimit;
    private $goodsLimit;
    private $seckillItemRepository;
    private $seckillService;
    private $grouponItemRepository;
    private $grouponService;
    private $freeService;
    private $multiGrouponService;

    public function __construct(GoodsRepository $goodsRepository
        , ProductRepository $productRepository
        , DiscountService $discountService
        , OrderRepository $orderRepository
        , CouponRepository $couponRepository
        , DiscountApplicator $discountApplicator
        , OrderProcessor $orderProcessor
        , BundleRepository $bundleRepository
        , BundleApplicator $bundleApplicator
        , PointRepository $pointRepository
        , PointApplicator $pointApplicator
        , Dispatcher $event
        , SuitRepository $suitRepository
        , SuitService $suitService
        , GoodsUserLimitRepository $goodsUserLimitRepository
        , GoodsLimitRepository $goodsLimitRepository
        , SeckillItemRepository $seckillItemRepository
        , SeckillService $seckillService
        , GrouponItemRepository $grouponItemRepository
        , GrouponService $grouponService
        , FreeService $freeService
        , MultiGrouponService $multiGrouponService
    )
    {
        $this->goodsRepository = $goodsRepository;
        $this->productRepository = $productRepository;
        $this->discountService = $discountService;
        $this->orderRepository = $orderRepository;
        $this->discountApplicator = $discountApplicator;
        $this->couponRepository = $couponRepository;
        $this->orderProcessor = $orderProcessor;
        $this->bundleRepository = $bundleRepository;
        $this->bundleApplicator = $bundleApplicator;
        $this->pointRepository = $pointRepository;
        $this->pointApplicator = $pointApplicator;
        $this->event = $event;
        $this->suitRepository = $suitRepository;
        $this->suitService = $suitService;
        $this->goodsUserLimit = $goodsUserLimitRepository;
        $this->goodsLimit = $goodsLimitRepository;
        $this->seckillItemRepository = $seckillItemRepository;
        $this->seckillService = $seckillService;
        $this->grouponItemRepository = $grouponItemRepository;
        $this->grouponService = $grouponService;
        $this->freeService = $freeService;
        $this->multiGrouponService = $multiGrouponService;
    }


    /**
     * 积分商城积分订单checkout方法
     */
    public function checkoutPoint()
    {
        try {
            $user = request()->user();

            if (!$user->mobile) { //如果用户没有绑定手机
                return $this->api([], false, 200, 'User unbind mobile');
            }

            if (empty(request('goods_id')) AND empty(request('product_id'))) {
                return $this->api([], false, 400, '请求错误，请选择商品');
            }

            $order = new Order(['user_id' => $user->id, 'type' => Order::TYPE_POINT, 'channel' => 'integral']);

            if (request('goods_id')) {
                $model = Goods::find(request('goods_id'));
            } else {
                $model = Product::find(request('product_id'));
            }

            if (!$model) {
                return $this->api([], false, 400, '商品不存在');
            }

            $quantity = request('quantity') ? request('quantity') : 1;

            if (!$model->getIsInSale($quantity)) {
                return $this->api([], false, 400, '库存不够');
            }

            if (!$model->is_largess) {
                return $this->api([], false, 400, '非积分兑换商品');
            }

            $userPoint = $this->pointRepository->getSumPointValid($user->id, 'default');

            if ($model->redeem_point > $userPoint) {
                return $this->api([], false, 400, '积分不够，无法兑换此商品');
            }

            $order->redeem_point = $model->redeem_point * $quantity;

            $item_meta = [
                'image' => $model->photo_url,
                'detail_id' => $model->detail_id
            ];

            $item_meta['specs_text'] = $model->specs_text;

            $orderItem = new OrderItem(['quantity' => $quantity, 'unit_price' => $model->sell_price,
                'item_id' => $model->id, 'type' => get_class($model), 'item_name' => $model->name, 'item_meta' => $item_meta
            ]);

            $orderItem->recalculateUnitsTotal();

            $order->addItem($orderItem);

            $defaultAddress = Address::getDefaultAddress(request()->user()->id);

            if (!$order = $this->orderProcessor->create($order)) {
                return $this->response()->errorForbidden('订单提交失败，请确认后重试');
            }

            $discounts = [];
            $coupons = [];

            return $this->api([
                'order' => $order,
                'discounts' => $discounts,
                'coupons' => $coupons,
                'address' => $defaultAddress,
                'invoice_status' => settings('invoice_status')
            ], true);

        } catch (\Exception $exception) {
            return $this->api([], false, 400, $exception->getMessage());
        }
    }

    public function checkout()
    {
        try {

            $user = request()->user();


            if (!$user->mobile AND !settings('other_disable_mobile')) { //如果用户没有绑定手机
                return $this->api([], false, 200, 'User unbind mobile');
            }

            //秒杀活动检测服务器压力
            if (!empty(request('seckill_item_id') And !$this->seckillService->checkSeckillMaxOnlineUser())) {
                return $this->api(['server_busy' => true], false, 500, '服务器繁忙');
            }


            $checkoutType = $this->getCheckoutType();

            $cartItems = call_user_func(array($this, 'getSelectedItemFrom' . $checkoutType));


            //1. 检查购物车的产品是否还有库存
            //$cartItems = $this->getSelectedItemInCart();

            /*if ($this->checkStock($cartItems) === false AND empty(request('goods_id'))) {
                return $this->api(['cart' => Cart::all()], false);
            }*/


            /*foreach ($cartItems as $key => $item) {

                if (is_null($item->model) OR !$item->model->getIsInSale($item->qty)) {

                    if (empty(request('suit_id'))) {
                        Cart::update($key, ['message' => '库存数量不足', 'status' => 'onhand']);
                    }

                    return $this->api([], false, 500, '商品: ' . $item->name . ' ' . $item->color . ',' . $item->size . ' 库存数量不足');
                }

                $goods_limit = $this->goodsLimit->findWhere(['goods_id' => $item->com_id, 'activity' => 1, ['starts_at', '<=', Carbon::now()], ['ends_at', '>=', Carbon::now()]])->first();
                if (!$goods_limit) {
                    continue;
                }

                $buy_num = $goods_limit->quantity;
                $check = $this->goodsUserLimit->findWhere(['user_id' => request()->user()->id, 'goods_id' => $item->com_id])->first();
                if ($check) {
                    $buy_num = $goods_limit->quantity - $check->buy_nums;
                }

                $start_at = strtotime($goods_limit->starts_at);
                $end_at = strtotime($goods_limit->ends_at);
                if (time() >= $start_at && time() <= $end_at && $item->qty > $buy_num) {

                    return $this->api([], false, 500, '商品: ' . $item->name . '每人限购 ' . $goods_limit->quantity . '件');
                }
            }*/


            //return $cartItems;
            $order = new Order(['user_id' => request()->user()->id]);

            if (request('type') AND request('type') == 'employee') {
                if ($user = request()->user('api') AND
                    $userRole = $user->roles->first() AND
                    $userRole->name == 'employee' AND
                    $staff = Staff::where('mobile', $user->mobile)->first() AND
                    $staff->active_status == 1
                ) {
                    $ruleGoods = $this->discountService->getGoodsByRole($userRole->name)['spu'];
                    foreach ($cartItems as $key => $item) {
                        if (!in_array($item->model->detail_id, $ruleGoods)) {
                            return $this->response()->errorForbidden('非法请求，包含非内购商品');
                        }
                    }
                } else {
                    return $this->response()->errorForbidden('非法请求，无权限进行此内购');
                }
                $order->order_no = build_order_no('NG');
                $order->type = Order::TYPE_IN_SOURCE;
            }

            //套餐
            if (!empty(request('suit_id'))) {
                $order->type = Order::TYPE_SUIT;
            }

            //秒杀
            if (!empty(request('seckill_item_id'))) {
                $order->type = Order::TYPE_SECKILL;

            }

            //拼团
            if (!empty(request('groupon_item_id'))) {
                $order->type = Order::TYPE_GROUPON;

            }

            //打call免费活动
            if (!empty(request('free_id'))) {
                $order->type = Order::TYPE_FREE_EVENT;
            }

            //小拼团
            if (!empty(request('multi_groupon_id'))) {
                $order->type = Order::TYPE_MULTI_GROUPON;
            }

            //2. 生成临时订单对象
            $order = $this->BuildOrderItemsFromCartItems($cartItems, $order);

            if ($goods_id = request('goods_id')) {
                $quantity = request('qty') ? request('qty') : 1;
                if ($quantity > 0) {
                    if (request('goods_type') AND request('goods_type') == 'sku') {
                        $product = Product::find($goods_id);
                        $orderItem = new OrderItem(['quantity' => $quantity, 'unit_price' => $product->sell_price,
                            'item_id' => $product->id, 'type' => Product::class, 'item_name' => $product->name, 'item_meta' => [
                                'image' => $product->photo_url
                                , 'detail_id' => $product->detail_id
                            ]]);
                    } else {
                        $goods = Goods::find($goods_id);
                        $orderItem = new OrderItem(['quantity' => $quantity, 'unit_price' => $goods->sell_price,
                            'item_id' => $goods->id, 'type' => Goods::class, 'item_name' => $goods->name, 'item_meta' => [
                                'image' => $goods->photo_url
                                , 'detail_id' => $goods->detail_id
                            ]]);
                    }

                    $orderItem->recalculateUnitsTotal();

                    $order->addItem($orderItem);
                }
            }


            $defaultAddress = Address::getDefaultAddress(request()->user()->id);

            if (!$order = $this->orderProcessor->create($order)) {
                return $this->response()->errorForbidden('订单提交失败，请确认后重试');
            }

            //3.get available discounts

            list($discounts, $in_source_discount_id, $bestDiscountAdjustmentTotal, $bestDiscountId, $cheap_price) = $this->getOrderDiscounts($order);

            //4. get available coupons
            list($coupons, $bestCouponID, $bestCouponAdjustmentTotal) = $this->getOrderCoupons($order, $user);


            $discountGroup = [];
            //$discountGroup = $this->discountService->getOrderDiscountGroup($order, new Collection($discounts), new Collection($coupons));

            //5.积分计算
            $orderPoint = $this->getOrderPoint($order, $user);


            if ($order->type == Order::TYPE_SUIT) { //如果是套餐订单不能使用促销活动和优惠券。
                $discounts = false;
                $coupons = [];
                $suit = $this->suitRepository->getSuitById(request('suit_id'));
                if ($suit And !$suit->use_point) { //套餐订单是否能够使用积分
                    $orderPoint = [];
                }
            }

            if ($order->type == Order::TYPE_SECKILL) { //如果是秒杀不能使用促销活动和优惠券。
                $discounts = false;
                $coupons = [];
                $seckill = $this->seckillItemRepository->getSeckillItemByID(request('seckill_item_id'));
                if ($seckill And !$seckill->use_point) {
                    $orderPoint = [];
                };
            }


            if ($order->type == Order::TYPE_FREE_EVENT OR $order->type == Order::TYPE_MULTI_GROUPON) { //如果是免费活动订单\小拼团订单不能使用促销活动和优惠券。
                $discounts = false;
                $coupons = [];
                $orderPoint = [];
            }

            //6.生成运费
            $order->payable_freight = 0;

            if ($order->type == Order::TYPE_SECKILL) {
                Adjustment::create(['order_id' => $order->id, 'order_item_id' => $order->items->first()->id,
                    'type' => 'seckill_discount', 'label' => '商品秒杀', 'amount' => 0, 'origin_type' => 'seckill_item', 'origin_id' => request('seckill_item_id')]);

            }

            //拼团
            if ($order->type == Order::TYPE_GROUPON) {
                SpecialType::create(['order_id' => $order->id, 'origin_type' => 'groupon_item', 'origin_id' => request('groupon_item_id')]);

                Adjustment::create(['order_id' => $order->id, 'order_item_id' => $order->items->first()->id,
                    'type' => 'groupon_discount', 'label' => '商品拼团', 'amount' => 0, 'origin_type' => 'groupon_item', 'origin_id' => request('groupon_item_id')]);
            }


            if ($order->type == Order::TYPE_SECKILL) {
                SpecialType::create(['order_id' => $order->id, 'origin_type' => 'seckill_item', 'origin_id' => request('seckill_item_id')]);
            }

            if ($order->type == Order::TYPE_SUIT) {
                SpecialType::create(['order_id' => $order->id, 'origin_type' => 'suit', 'origin_id' => request('suit_id')]);

            }

            if ($order->type == Order::TYPE_MULTI_GROUPON) {
                SpecialType::create(['order_id' => $order->id, 'origin_type' => 'multi_groupon', 'origin_id' => request('multi_groupon_id')]);
            }

            return $this->api([
                'order' => $order,
                'discounts' => $discounts,
                'coupons' => $coupons,
                'address' => $defaultAddress,
                'in_source_discount_id' => $in_source_discount_id,
                'orderPoint' => $orderPoint,
                'discountGroup' => $discountGroup,
                'invoice_status' => settings('invoice_status'),
                'best_discount_id' => $bestDiscountId,
                'best_coupon_id' => $bestCouponID,
                'best_coupon_adjustment_total' => $bestCouponAdjustmentTotal,
                'best_discount_adjustment_total' => $bestDiscountAdjustmentTotal
            ], true);


        } catch (\Exception $exception) {
            \Log::info($exception->getTraceAsString());
            return $this->api([], false, 400, $exception->getMessage());
        }
    }

    /**
     * confirm the order to be waiting to pay
     */
    public function confirm()
    {
        $user = request()->user();

        //TODO: 1. need to confirm whether the products are still in stock
        $order_no = request('order_no');
        if (!$order_no || !$order = $this->orderRepository->getOrderByNo($order_no)) {
            return $this->api([], false, 500, '订单不存在');
        }

        if ($user->cant('submit', $order)) {
//            return $this->api([], false, 500, 'You have no right to submit this order.');
            return $this->api([], false, 500, '订单提交失败，订单内含有销售价低于商家价格保护设定的商品，请替换商品或联系商家');
        }

        if ($note = request('note')) {
            $order->note = $note;
        }

        if (($order->type == Order::TYPE_SUIT AND request('point') AND !$order->specialTypes()->first()->suit->use_point) || (request('point') AND !settings('order_use_point_enabled'))) {
            return $this->api([], false, 500, '该套餐不能使用积分');
        }

        if (($order->type == Order::TYPE_SECKILL AND request('point') AND !$order->specialTypes()->first()->seckill_item->use_point || (request('point') AND !settings('order_use_point_enabled')))) {
            return $this->api([], false, 500, '该秒杀商品不能使用积分');
        }

        if (($order->type == Order::TYPE_GROUPON AND request('point') AND !$order->specialTypes()->first()->groupon_item->use_point || (request('point') AND !settings('order_use_point_enabled')))) {
            return $this->api([], false, 500, '该拼团商品不能使用积分');
        }

        if (($order->type == Order::TYPE_FREE_EVENT AND request('point') || (request('point') AND !settings('order_use_point_enabled')))) {
            return $this->api([], false, 500, '该免费领取商品不能使用积分');
        }

        if (($order->type == Order::TYPE_MULTI_GROUPON AND request('point'))) {
            return $this->api([], false, 500, '该拼团商品不能使用积分');
        }

        foreach ($order->getItems() as $item) { // 再次checker库存
            $model = $item->type;
            $model = new $model();

            $product = $model->find($item->item_id);
            $goods = Goods::find($product->goods_id);
            if (!$product->getIsInSale($item->quantity)) {
                $specs_value = '';
                foreach ($product->specID as $item) {
                    $str = SpecsValue::find(['id' => intval($item)])->first()->name;
                    $specs_value .= $str . ',';
                }

                return $this->api([], false, 500, '商品: ' . $goods->name . ' ' . trim($specs_value, ',') . ' 库存不够，请重新下单');
            }
        }

        try {

            DB::beginTransaction();

            if ($order->type == Order::TYPE_IN_SOURCE) {
                //员工内购处理
                //此订单消费额度
                if ($userRole = $user->roles->first()
                    AND $staff = Staff::where('mobile', $user->mobile)->first()
                    AND $staff->active_status == 1
                ) {

                    $channelDiscount = $this->discountService->getGoodsByRole($userRole->name);
                    $ruleGoods = $channelDiscount['spu'];
                    $percentageGroup = $channelDiscount['percentageGroup'];
                    $channelRole = true;

                    //3.get available discounts
                    $discounts = $channelDiscount['discounts'];

                    $amount = $order->total;

                    //计算本月剩余额度
                    $orderTable = 'el_order';
                    $orderItemTable = 'el_order_item';
                    $adjustmentTable = 'el_order_adjustment';
                    $amountUsed = Order::join($orderItemTable, $orderTable . '.id', '=', $orderItemTable . '.order_id')
                        //->join($adjustmentTable, $orderTable . '.id', '=', $adjustmentTable . '.order_id')
                        ->whereRaw('DATE_ADD(curdate(),interval -day(curdate())+1 day) < ' . $orderTable . '.created_at')//本月
                        ->where($orderTable . '.type', '=', 2)
                        ->where($orderTable . '.user_id', '=', $user->id)
                        ->where($orderTable . '.pay_status', '=', 1)
                        ->sum($orderItemTable . '.units_total');
                    $amount = $amount / 100;
                    $amountUsed = $amountUsed / 100;

                    $refundTable = 'el_refund';

                    $refundAmount = Refund::join($orderTable, $orderTable . '.id', '=', $refundTable . '.order_id')
                        ->join($orderItemTable, $refundTable . '.order_item_id', '=', $orderItemTable . '.id')
                        ->whereRaw('DATE_ADD(curdate(),interval -day(curdate())+1 day) < ' . $orderTable . '.pay_time')//本月
                        ->where($refundTable . '.user_id', '=', $user->id)
                        ->where($refundTable . '.status', '=', Refund::STATUS_COMPLETE)
                        ->sum($orderItemTable . '.units_total');

                    $refundAmount = $refundAmount / 100;

                    $limit = settings('employee_discount_limit') ? settings('employee_discount_limit') : 0;
                    $limit = $limit - $amountUsed + $refundAmount;

                    //判断额度
                    if ($limit < $amount) {
                        return $this->response()->errorForbidden('员工内购额度不足，剩余额度：' . $limit . '，需要额度：' . $amount);
                    }

                    foreach ($discounts as $roleDiscount) {
                        $this->discountApplicator->apply($order, $roleDiscount);
                    }

                } else {
                    return $this->api(false, 400, '内购折扣信息有误，请确认后重试', []);
                }
            } else {
                //3. apply the available discounts
                $discount = Discount::find(request('discount_id'));
                if (!empty($discount)) {
                    if ($this->discountService->checkDiscount($order, $discount)) {

                        $order->type = Order::TYPE_DISCOUNT;

                        $this->discountApplicator->apply($order, $discount);

                    } else {
                        return $this->api([], false, 500, '折扣信息有误，请确认后重试');
                    }
                }

                if (empty($discount) OR $discount->exclusive != 1) {
                    //4. apply the available coupons
                    $coupon = Coupon::find(request('coupon_id'));
                    if (!empty($coupon)) {
                        if ($coupon->used_at != null) {
                            return $this->api([], false, 500, '此优惠券已被使用');
                        }
                        if ($user->can('update', $coupon) AND $this->discountService->checkCoupon($order, $coupon)) {
                            $this->discountApplicator->apply($order, $coupon);
                        } else {
                            return $this->api([], false, 500, '优惠券信息有误，请确认后重试');
                        }
                    }
                }
            }

            if ($order->type == Order::TYPE_POINT) {

                $userPoint = $this->pointRepository->getSumPointValid($user->id, 'default');

                if (request('point') > $userPoint) {
                    return $this->api([], false, 500, '积分不够');
                }

                if (request('point') < $order->redeem_point) {
                    return $this->api([], false, 500, '积分不够');
                }

                $this->pointRepository->create([
                    'user_id' => $order->user_id,
                    'action' => 'order_point',
                    'note' => '积分订单',
                    'value' => (-1) * request('point'),
                    'valid_time' => 0,
                    'item_type' => 'GuoJiangClub\Catering\Component\Order\Models\Order',
                    'item_id' => $order->id
                ]);
                event('point.change', $user->id);

                $user = User::find($user->id);
                $user->notify(new PointRecord(['point' => [
                    'user_id' => $order->user_id,
                    'action' => 'order_point',
                    'note' => '积分商城购物，使用积分：',
                    'value' => request('point'),
                    'valid_time' => 0,
                    'item_type' => 'GuoJiangClub\Catering\Component\Order\Models\Order',
                    'item_id' => $order->id,
                ]]));

            } elseif (request('point') AND settings('point_enabled')) {
                //1. apply the point discount
                $point = request('point');
                if ($this->pointRepository->checkUserPoint($order, $point)) {
                    $res = $this->pointApplicator->apply($order, $point);
                    if (!$res) {
                        return $this->api([], false, 500, '积分处理出错');
                    }
                } else {
                    return $this->api([], false, 500, '积分不足或不满足积分折扣规则');
                }

                //9. 创建积分使用信息
                if (request('point') AND settings('point_enabled')) {
                    $this->pointRepository->create([
                        'user_id' => $order->user_id,
                        'action' => 'order_discount',
                        'note' => '订单使用积分折扣',
                        'value' => (-1) * request('point'),
                        'valid_time' => 0,
                        'item_type' => 'GuoJiangClub\Catering\Component\Order\Models\Order',
                        'item_id' => $order->id
                    ]);
                    event('point.change', $user->id);

                    $user = User::find($user->id);
                    $user->notify(new PointRecord(['point' => [
                        'user_id' => $order->user_id,
                        'action' => 'order_discount',
                        'note' => '订单使用积分折扣',
                        'value' => request('point'),
                        'valid_time' => 0,
                        'item_type' => 'GuoJiangClub\Catering\Component\Order\Models\Order',
                        'item_id' => $order->id
                    ]]));
                }
            }

            //5. 保存收获地址信息。
            if ($address = Address::find(request('address_id'))) {
                $order->accept_name = $address->accept_name;
                $order->mobile = $address->mobile;
                $order->address = $address->address;
                $order->address_name = $address->address_name;
            }
            $order->source = request('source');


            //5. 保存订单状态
            $this->orderProcessor->process($order);

            //6. remove goods store.
            foreach ($order->getItems() as $item) {
                $model = $item->type;
                $model = new $model();
                $product = $model->find($item->item_id);
                $product->reduceStock($item->quantity);
                $product->increaseSales($item->quantity);
                $product->save();
            }

            //7. 保存发票信息
            if ($invoice_id = request('invoice_id') AND $invoice = InvoiceUser::find($invoice_id)) {
                InvoiceOrder::create(['order_id' => $order->id,
                    'type' => $invoice->type, 'title' => $invoice->title, 'content' => $invoice->content,
                    'consignee_phone' => $invoice->consignee_phone, 'consignee_email' => $invoice->consignee_email]);
            }

            //8. 移除购物车中已下单的商品
            foreach ($order->getItems() as $orderItem) {
                if ($carItem = Cart::search(['name' => $orderItem->item_name])->first()) {
                    Cart::remove($carItem->rawId());
                }
            }

            if ($order->type == Order::TYPE_FREE_EVENT) {
                SpecialType::create(['order_id' => $order->id, 'origin_type' => 'free_event', 'origin_id' => request('task_id')]);
            }

            DB::commit();

            return $this->api(['order' => $order], true);

        } catch (\Exception $exception) {
            DB::rollBack();
            \Log::info($exception->getMessage() . $exception->getTraceAsString());
            return $this->response()->errorInternal($exception->getMessage());
        }
    }


    public function confirmPoint()
    {
        $user = request()->user();

        //TODO: 1. need to confirm whether the products are still in stock
        $order_no = request('order_no');
        if (!$order_no || !$order = $this->orderRepository->getOrderByNo($order_no)) {
            return $this->response()->errorBadRequest('订单不存在');
        }

        if ($user->cant('submit', $order)) {
            return $this->response()->errorForbidden('You have no right to submit this order.');
        }

        if ($note = request('note')) {
            $order->note = $note;
        }

        foreach ($order->getItems() as $item) { // 再次checker库存
            $model = $item->type;
            $model = new $model();

            $product = $model->find($item->item_id);

            if (!$product->getIsInSale($item->quantity)) {
                return $this->response()->errorForbidden('商品库存不够，请重新下单');
            }

            if (!$product->is_largess) {
                return $this->api([], false, 400, '非积分兑换商品');
            }
        }

        try {

            DB::beginTransaction();

            $userPoint = $this->pointRepository->getSumPointValid($user->id, 'default');

            if ($userPoint < $order->redeem_point) {
                return $this->api([], false, 400, '积分不够');
            }

            $point = $this->pointRepository->create([
                'user_id' => $order->user_id,
                'action' => 'order_point',
                'note' => '积分订单',
                'value' => (-1) * $order->redeem_point,
                'valid_time' => 0,
                'item_type' => Order::class,
                'item_id' => $order->id
            ]);

            event('point.change', $user->id);

            //5. 保存收获地址信息。
            if ($address = Address::find(request('address_id'))) {
                $order->accept_name = $address->accept_name;
                $order->mobile = $address->mobile;
                $order->address = $address->address;
                $order->address_name = $address->address_name;
            }

            //$user->notify((new PointChanged(compact('point')))->delay(Carbon::now()->addSecond(30)));
            event('st.wechat.message.point', [$user, '购物消耗积分', $order->redeem_point]);

            //5. 保存订单状态
            $order->status = Order::STATUS_PAY;
            $order->submit_time = Carbon::now();
            $order->pay_time = Carbon::now();
            $order->pay_status = 1;
            $order->source = request('source');
            $order->save();

            //6. remove goods store.
            foreach ($order->getItems() as $item) {
                $model = $item->type;
                $model = new $model();
                $product = $model->find($item->item_id);
                $product->reduceStock($item->quantity);
                $product->increaseSales($item->quantity);
                $product->save();
            }

            //7. 保存发票信息
            if ($invoice_id = request('invoice_id') AND $invoice = InvoiceUser::find($invoice_id)) {
                InvoiceOrder::create(['order_id' => $order->id,
                    'type' => $invoice->type, 'title' => $invoice->title, 'content' => $invoice->content,
                    'consignee_phone' => $invoice->consignee_phone, 'consignee_email' => $invoice->consignee_email]);
            }

            event('order.customer.paid', [$order]);

            DB::commit();

            return $this->api(['order' => $order], true);

        } catch (\Exception $exception) {
            DB::rollBack();
            \Log::info($exception->getMessage() . $exception->getTraceAsString());
            return $this->response()->errorInternal($exception->getMessage());
        }
    }

    /**
     * cancel this order
     */
    public function cancel()
    {
        $user = request()->user();

        $order_no = request('order_no');
        if (!$order_no || !$order = $this->orderRepository->getOrderByNo($order_no)) {
            return $this->response()->errorBadRequest('订单不存在');
        }

        if ($user->cant('cancel', $order)) {
            return $this->response()->errorForbidden('You have no right to cancel this order.');
        }

        $this->orderProcessor->cancel($order);

        //TODO: 用户未付款前取消订单后，需要还原库存
        foreach ($order->getItems() as $item) {
            $model = $item->type;
            $model = new $model();
            $product = $model->find($item->item_id);
            $product->restoreStock($item->quantity);
            $product->restoreSales($item->quantity);
            $product->save();
        }

        return $this->api([], true, 200, '订单取消成功');
    }

    /**
     * received this order
     */
    public function received()
    {
        try {

            DB::beginTransaction();
            $user = request()->user();

            $order_no = request('order_no');
            if (!$order_no || !$order = $this->orderRepository->getOrderByNo($order_no)) {
                return $this->response()->errorBadRequest('订单不存在');
            }

            if ($user->cant('received', $order)) {
                return $this->response()->errorForbidden('You have no right to received this order.');
            }

            $this->orderProcessor->received($order);

            DB::commit();

            return $this->api([], true, 200, '确认收货操作成功');

        } catch (\Exception $exception) {
            DB::rollBack();
            \Log::info($exception->getMessage() . $exception->getTraceAsString());
            $this->response()->errorInternal($exception->getMessage());
        }
    }

    public function delete()
    {
        $user = request()->user();

        $order_no = request('order_no');
        if (!$order_no || !$order = $this->orderRepository->getOrderByNo($order_no)) {
            return $this->response()->errorBadRequest('订单不存在');
        }

        if ($user->cant('delete', $order)) {
            return $this->response()->errorForbidden('You have no right to delete this order.');
        }

        $this->orderProcessor->delete($order);

        return $this->api([], true, 200, '删除无效订单成功');
    }

    public function review()
    {
        $user = request()->user();
        $comments = request()->except('_token');

        if (!is_array($comments)) {
            return $this->response()->errorBadRequest('提交参数错误');
        }

        foreach ($comments as $key => $comment) {

            if (!isset($comment['order_no']) OR !$order = $this->orderRepository->getOrderByNo($comment['order_no'])) {
                return $this->response()->errorBadRequest('订单 ' . $comment['order_no'] . ' 不存在');
            }

            if (!isset($comment['order_item_id']) OR !$orderItem = OrderItem::find($comment['order_item_id'])) {
                return $this->response()->errorForbidden('You need to pass into specific order_item_id');
            }

            if ($user->cant('review', [$order, $orderItem])) {
                return $this->response()->errorForbidden('You have no right to review this order.');
            }

            if ($order->comments()->where('order_item_id', $comment['order_item_id'])->count() > 0) {
                return $this->response()->errorForbidden('该产品已经评论，无法再次评论');
            }

            $content = isset($comment['contents']) ? $comment['contents'] : '';
            $point = isset($comment['point']) ? $comment['point'] : 5;
            $pic_list = isset($comment['images']) ? $comment['images'] : [];

            $comment = new Comment(['user_id' => $user->id, 'order_item_id' => $comment['order_item_id']
                , 'item_id' => $orderItem->item_id, 'item_meta' => $orderItem->item_meta
                , 'contents' => $content, 'point' => $point
                , 'status' => settings('') == 'show' ? 'show' : 'hidden'
                , 'pic_list' => $pic_list
                , 'goods_id' => $orderItem->item_meta['detail_id']
            ]);

            $order->comments()->save($comment);

            event('order.item.commented', $orderItem);
            event('product.commented', [$orderItem, $point]);

            $this->orderProcessor->process($order);
        }

        return $this->api([], true, 200, '订单评价成功');
    }


    private function getSelectedItemInCart()
    {
        $user = request()->user();

        //获取购物车中选中的商品数据
        if ($ids = request('cart_ids') AND count($ids) > 0) {

            $cartItems = new Collection();

            foreach ($ids as $cartId) {
                $cartItems->put($cartId, Cart::get($cartId));
            }

            return $cartItems;
            // 套餐
        } elseif (!empty(request('suit_id'))) {

            $buys = request()->all();

            if (!$suits = $this->suitRepository->getSuitById(request('suit_id'))) {
                throw new \Exception('套餐不存在');
            }

            if (!$this->suitService->checkOrderSuitInfo($buys, $suits)) {
                throw new \Exception('下单商品和套餐信息不匹配');
            }
            unset($buys['suit_id']);

            $cartItems = new Collection();

            foreach ($buys as $k => $item) {

                $__raw_id = md5(time() . $k);

                $input = ['__raw_id' => $__raw_id,
                    'com_id' => isset($item['id']) ? $item['id'] : '',
                    'name' => isset($item['name']) ? $item['name'] : '',
                    'img' => isset($item['img']) ? $item['img'] : '',
                    'qty' => isset($item['qty']) ? $item['qty'] : '',
                    'price' => isset($item['price']) ? $item['price'] : '',
                    'total' => isset($item['total']) ? $item['total'] : '',
                ];

                if (isset($item['attributes']['dynamic_sku'])) {
                    $input['color'] = isset($item['attributes']['dynamic_sku']['color']) ? $item['attributes']['dynamic_sku']['color'] : [];
                    $input['size'] = isset($item['attributes']['dynamic_sku']['size']) ? $item['attributes']['dynamic_sku']['size'] : [];
                    $input['id'] = isset($item['attributes']['dynamic_sku']['id']) ? $item['attributes']['dynamic_sku']['id'] : [];
                    $input['type'] = 'sku';
                    $input['__model'] = 'ElementVip\Component\Product\Models\Product';
                } else {
                    $input['size'] = isset($item['size']) ? $item['size'] : '';
                    $input['color'] = isset($item['color']) ? $item['color'] : '';
                    $input['type'] = 'spu';
                    $input['__model'] = 'ElementVip\Component\Product\Models\Goods';
                }

                $data = new Item(array_merge($input), $item);

                $cartItems->put(md5(time() . $k), $data);

            }

            // 秒杀活动
        } elseif (!empty(request('seckill_item_id'))) {
            $buys = request()->all();

            $this->seckillService->checkOrderSeckillInfo($buys, $user->id);


            $cartItems = $this->seckillService->makeCartItems($buys);

            //拼团活动
        } elseif (!empty(request('groupon_item_id'))) {

            $buys = request()->all();

            $this->grouponService->checkOrderGrouponInfo($buys, $user->id);

            $cartItems = $this->grouponService->makeCartItems($buys);

        } else {
            $cartItems = Cart::all();
        }

        return $cartItems;
    }

    /**
     * call by call_user_func()
     * @return bool|Collection
     * @throws \Exception
     */
    private function getSelectedItemFromCart()
    {
        //获取购物车中选中的商品数据

        $ids = request('cart_ids');

        if (!$ids || count($ids) == 0)
            return false;


        $cartItems = new Collection();

        foreach ($ids as $cartId) {
            $cartItems->put($cartId, Cart::get($cartId));
        }

        foreach ($cartItems as $key => $item) {

            //检查库存是否足够
            if (!$this->checkItemStock($item)) {

                Cart::update($key, ['message' => '库存数量不足', 'status' => 'onhand']);

                throw new \Exception('商品: ' . $item->name . ' ' . $item->color . ',' . $item->size . ' 库存数量不足');
            }

            $this->checkItemLimit($item);
        }

        return $cartItems;

    }

    /**
     * call by call_user_func()
     * @return Collection
     * @throws \Exception
     */
    private function getSelectedItemFromSuit()
    {
        $cartItems = new Collection();

        if (!empty(request('suit_id'))) {

            $buys = request()->all();

            if (!$suits = $this->suitRepository->getSuitById(request('suit_id'))) {
                throw new \Exception('套餐不存在');
            }

            if (!$this->suitService->checkOrderSuitInfo($buys, $suits)) {
                throw new \Exception('下单商品和套餐信息不匹配');
            }
            unset($buys['suit_id']);

            foreach ($buys as $k => $item) {

                $__raw_id = md5(time() . $k);

                /*eddy:一定要有ID，否则检查SPU库存会报错*/
                if (!isset($item['id'])) continue;

                $input = ['__raw_id' => $__raw_id,
                    'com_id' => isset($item['id']) ? $item['id'] : '',  //goods_id
                    'name' => isset($item['name']) ? $item['name'] : '',
                    'img' => isset($item['img']) ? $item['img'] : '',
                    'qty' => isset($item['qty']) ? $item['qty'] : '',
                    'price' => isset($item['price']) ? $item['price'] : '',
                    'total' => isset($item['total']) ? $item['total'] : '',
                ];

                if (isset($item['attributes']['dynamic_sku'])) {
                    $input['color'] = isset($item['attributes']['dynamic_sku']['color']) ? $item['attributes']['dynamic_sku']['color'] : [];
                    $input['size'] = isset($item['attributes']['dynamic_sku']['size']) ? $item['attributes']['dynamic_sku']['size'] : [];
                    $input['id'] = isset($item['attributes']['dynamic_sku']['id']) ? $item['attributes']['dynamic_sku']['id'] : [];
                    $input['type'] = 'sku';
                    $input['__model'] = 'ElementVip\Component\Product\Models\Product';
                } else {
                    $input['size'] = isset($item['size']) ? $item['size'] : '';
                    $input['color'] = isset($item['color']) ? $item['color'] : '';
                    $input['type'] = 'spu';
                    $input['__model'] = 'ElementVip\Component\Product\Models\Goods';

                    /*eddy*/
                    $input['id'] = $item['id'];
                }

                $data = new Item(array_merge($input), $item);

                $cartItems->put(md5(time() . $k), $data);

            }

            // 秒杀活动
        }

        foreach ($cartItems as $key => $item) {

            //检查库存是否足够
            if (!$this->checkItemStock($item)) {
                throw new \Exception('商品: ' . $item->name . ' ' . $item->color . ',' . $item->size . ' 库存数量不足');
            }
        }

        return $cartItems;
    }

    /**
     * call by call_user_func()
     * @return Collection
     * @throws \Exception
     */
    private function getSelectedItemFromSeckill()
    {

        $user = request()->user();

        $cartItems = new Collection();
        if (!empty(request('seckill_item_id'))) {
            $buys = request()->all();

            $this->seckillService->checkOrderSeckillInfo($buys, $user->id);

            $cartItems = $this->seckillService->makeCartItems($buys);

            //拼团活动
        }

        foreach ($cartItems as $key => $item) {

            //检查库存是否足够
            if (!$this->checkItemStock($item)) {
                throw new \Exception('商品: ' . $item->name . ' ' . $item->color . ',' . $item->size . ' 库存数量不足');
            }
        }

        return $cartItems;
    }

    /**
     * call by call_user_func()
     * @return Collection
     * @throws \Exception
     */
    private function getSelectedItemFromGroupon()
    {
        $cartItems = new Collection();

        if (empty(request('groupon_item_id')) || empty(request('single'))) {
            return $cartItems;
        }

        $item = request('single');
        $grouponItemId = request('groupon_item_id');

        if (!isset($item['id']) || !isset($item['qty'])) {
            throw new \Exception('拼团商品数据不存在');
        }

        //判断拼团活动是否在进行中
        $grouponItem = $this->grouponItemRepository->findActiveById($grouponItemId);

        if (!$grouponItem) {

            throw new \Exception('拼团商品数据不存在');
        }

        //判断拼团活动是否已经成团

        $user = request()->user();

        $this->grouponService->checkGrouponItemSales($grouponItem);

        $this->grouponService->checkGrouponItemLimit($grouponItem, $user->id, $item['qty']);

        $cartItems = $this->grouponService->makeCartItems($item, $grouponItem);

        foreach ($cartItems as $key => $item) {

            //检查库存是否足够
            if (!$this->checkItemStock($item)) {
                throw new \Exception('商品: ' . $item->name . ' ' . $item->color . ',' . $item->size . ' 库存数量不足');
            }
        }

        return $cartItems;
    }

    /**
     * call by call_user_func free
     * @return Collection
     * @throws \Exception
     */
    private function getSelectedItemFromFreeEvent()
    {

        $user = request()->user();

        $cartItems = new Collection();
        if (!empty(request('free_id'))) {
            $buys = request()->all();
            $this->freeService->checkCanCreateOrder($user->id, request('free_id'), request('task_id'));
            $cartItems = $this->freeService->makeCartItems($buys);
        }

        foreach ($cartItems as $key => $item) {
            //检查库存是否足够
            if (!$this->checkItemStock($item)) {
                throw new \Exception('商品: ' . $item->name . ' ' . $item->color . ',' . $item->size . ' 库存数量不足');
            }
        }

        return $cartItems;
    }

    /**
     * 小拼团数据检测
     * @return Collection
     * @throws \Exception
     */
    private function getSelectedItemFromMultiGroupon()
    {
        $user = request()->user();
        $cartItems = new Collection();
        if ($multiGrouponID = request('multi_groupon_id')) {
            \Log::info($multiGrouponID);
            $buys = request()->all();

            $this->multiGrouponService->checkGrouponStatusByUser($user->id, $multiGrouponID, request('multi_groupon_item_id'));

            $cartItems = $this->multiGrouponService->makeCartItems($buys, $multiGrouponID);
        }

        foreach ($cartItems as $key => $item) {
            //检查库存是否足够
            if (!$this->checkItemStock($item)) {
                throw new \Exception('商品: ' . $item->name . ' ' . $item->color . ',' . $item->size . ' 库存数量不足');
            }
        }
        return $cartItems;
    }


    private function checkStock($cartItems)
    {
        $flag = true;

        if (count($cartItems) == 0)
            return false;

        foreach ($cartItems as $key => $item) {
            /*if (!$goods = $this->goodsRepository->findOneById($item->id)) {
                $flag = false;
                Cart::update($key, ['message' => '商品已下架', 'status' => 'offline']);
            } else {*/
            /*if ($item->model) {
                $product = $this->productRepository->find($item->sku);
            } else {
                $product = $this->goodsRepository->find($item->id);
            }*/
            if (is_null($item->model) OR !$item->model->getIsInSale($item->qty)) {
                if (empty(request('suit_id'))) {
                    Cart::update($key, ['message' => '库存数量不够', 'status' => 'onhand']);
                }
                $flag = false;
            }
        }
        return $flag;
    }

    private function checkItemStock($item)
    {
        if (is_null($item->model) || !$item->model->getIsInSale($item->qty)) {
            return false;
        }
        return true;
    }

    private function checkItemLimit($item)
    {

        $goods_limit = $this->goodsLimit->findWhere(['goods_id' => $item->com_id, 'activity' => 1, ['starts_at', '<=', Carbon::now()], ['ends_at', '>=', Carbon::now()]])->first();
        if (!$goods_limit)
            return true;

        $buy_num = $goods_limit->quantity;

        $check = $this->goodsUserLimit->findWhere(['user_id' => request()->user()->id, 'goods_id' => $item->com_id])->first();
        if ($check) {
            $buy_num = $goods_limit->quantity - $check->buy_nums;
        }

        $start_at = strtotime($goods_limit->starts_at);
        $end_at = strtotime($goods_limit->ends_at);
        if (time() >= $start_at && time() <= $end_at && $item->qty > $buy_num) {
            throw new \Exception('商品: ' . $item->name . '每人限购 ' . $goods_limit->quantity . '件');
        }

        return true;
    }

    public function extraInfo()
    {
        $user = request()->user();
        $point = $this->pointRepository->getSumPointValid($user->id, 'default');
        $pointToMoney = settings('point_proportion') ? settings('point_proportion') : 0;
        $pointLimit = settings('point_order_limit') ? settings('point_order_limit') : 1;
        return $this->api([
            'userPoint' => $point,
            'pointToMoney' => $pointToMoney / 1,
            'pointLimit' => $pointLimit / 100
        ]);
    }

    /**
     * 实时计算优惠信息
     * @return Response|void
     */
    public function calculateDiscount()
    {
        $user = request()->user();

        //TODO: 1. need to confirm whether the products are still in stock
        $order_no = request('order_no');
        if (!$order_no || !$order = $this->orderRepository->getOrderByNo($order_no)) {
            return $this->response()->errorBadRequest('订单不存在');
        }

        if ($user->cant('submit', $order)) {
            return $this->response()->errorForbidden('You have no right to submit this order.');
        }

        if (empty(request('discount_id')) AND empty(request('coupon_id'))) {
            return $this->response()->errorForbidden('请选择促销活动或者优惠券');
        }

        $adjustmentTotal = 0;
        $discounts = [];
        $coupons = [];

        if ($discount = Discount::find(request('discount_id'))) {
            $adjustmentTotal = $adjustmentTotal + $this->discountService->calculateDiscounts($order, $discount);
            if (empty(request('coupon_id'))) {
                $coupons = $this->discountService->getEligibilityCoupons($order, $user->id);
            }
        }


        if ($coupon = Coupon::find(request('coupon_id'))) {
            $adjustmentTotal = $adjustmentTotal + $this->discountService->calculateDiscounts($order, $coupon);
            if (empty(request('discount_id'))) {
                $discounts = $this->discountService->getEligibilityDiscounts($order);
            }
        }
        //5.积分计算
        $orderPoint = [];

        if (settings('point_enabled') AND settings('point_proportion')) {
            $orderPoint['userPoint'] = $this->pointRepository->getSumPointValid($user->id, 'default'); //用户可用积分
            $orderPoint['pointToMoney'] = settings('point_proportion') ? settings('point_proportion') : 0;  //pointToMoney
            $orderPoint['pointLimit'] = (settings('point_order_limit') ? settings('point_order_limit') : 1) / 100; //pointLimit
            $pointAmount = min($orderPoint['userPoint'] * $orderPoint['pointToMoney'] * $orderPoint['pointLimit'], $order->total);
            $orderPoint['pointAmount'] = -$pointAmount;
            $orderPoint['pointCanUse'] = $pointAmount / $orderPoint['pointToMoney'];
        }


        return $this->api([
            'adjustmentTotal' => $adjustmentTotal,
            'coupons' => $coupons,
            'discounts' => $discounts,
            'orderPoint' => $orderPoint,
        ]);
    }


    private function getCheckoutType()
    {
        if ($ids = request('cart_ids') AND count($ids) > 0)
            return 'Cart';
        if (request('suit_id'))
            return 'Suit';
        if (request('seckill_item_id'))
            return 'Seckill';
        if (request('groupon_item_id'))
            return 'Groupon';
        if (request('free_id'))
            return 'FreeEvent';
        if (request('multi_groupon_id'))
            return 'MultiGroupon';
        return 'Default';
    }

    /**
     * get order discounts data.
     * @param $order
     * @return array
     */
    private function getOrderDiscounts($order)
    {
        $bestDiscountAdjustmentTotal = 0;
        $bestDiscountId = 0;
        $cheap_price = 0;
        //以下几种订单类型一律不能使用促销活动
        if ($order->type == Order::TYPE_SUIT
            || $order->type == Order::TYPE_SECKILL
            || $order->type == Order::TYPE_GROUPON
            || $order->type == Order::TYPE_FREE_EVENT
        ) {
            $discounts = false;
            $in_source_discount_id = false;

            return array($discounts, $in_source_discount_id, $bestDiscountAdjustmentTotal, $bestDiscountId, $cheap_price);
        }


        $discounts = $this->discountService->getEligibilityDiscounts($order);
        $in_source_discount_id = false;

        if ($discounts) {
            $discounts = $discounts->filter(function ($item) use ($order, &$in_source_discount_id) {
                if ($order->type == Order::TYPE_IN_SOURCE) {
                    $flag = false;
                    foreach ($item->rules as $rule) {
                        if ($rule->type == 'contains_role' AND $rule->getRoleName() == 'employee') {
                            $flag = true;
                            $in_source_discount_id = $item->id;
                        }
                    }
                    return $flag;
                } else {
                    foreach ($item->rules as $rule) {
                        if ($rule->type == 'contains_role' AND $rule->getRoleName() == 'employee') {
                            return false;
                        }
                    }
                    return true;
                }
            });
            if (count($discounts) == 0) { //修复过滤后discount为0时非false 的问题。
                $discounts = false;
            } else {
                $bestDiscount = $discounts->sortBy('adjustmentTotal')->first();
                $bestDiscountId = $bestDiscount->id;
                $cheap_price += $bestDiscount->adjustmentTotal;
                $bestDiscountAdjustmentTotal = -$bestDiscount->adjustmentTotal;

                $discounts = collect_to_array($discounts);
            }
        }
        return array($discounts, $in_source_discount_id, $bestDiscountAdjustmentTotal, $bestDiscountId, $cheap_price);
    }

    /**
     * @param $order
     * @param $user
     * @return array|bool
     */
    private function getOrderCoupons($order, $user)
    {
        $bestCouponID = 0;
        $bestCouponAdjustmentTotal = 0;
        $cheap_price = 0;

        //以下几种订单类型一律不能使用促销活动
        if ($order->type == Order::TYPE_SUIT
            || $order->type == Order::TYPE_SECKILL
            || $order->type == Order::TYPE_GROUPON
            || $order->type == Order::TYPE_FREE_EVENT
        ) {

            return array([], $bestCouponID, $bestCouponAdjustmentTotal);
        }

        $coupons = $this->discountService->getEligibilityCoupons($order, $user->id);

        if ($coupons AND $coupons->where('discount.code', 'vipeaknewonline')->first()) {

            $isRemoveCoupon = false;

            foreach ($order->getItems() as $item) {
                $sku = $item->getItemKey();

                if ($condition = SingleDiscountCondition::where('name', $sku)->whereHas('discount', function ($query) {
                    return $query->where('status', 1)
                        ->where('ends_at', '>', Carbon::now());
                })->first()
                ) {
                    $isRemoveCoupon = true;
                }
            }

            if ($isRemoveCoupon) {
                $coupons = $coupons->filter(function ($item) {
                    return $item->discount->code != 'vipeaknewonline';
                });
            }
        }

        if ($coupons AND $order->type != Order::TYPE_IN_SOURCE) {

            $bestCoupon = $coupons->sortBy('adjustmentTotal')->first();
            if ($bestCoupon->orderAmountLimit > 0 AND $bestCoupon->orderAmountLimit > ($order->total + $cheap_price)) {
                $bestCouponID = 0;
            } else {
                $bestCouponID = $bestCoupon->id;
                $cheap_price += $bestCoupon->adjustmentTotal;
                $bestCouponAdjustmentTotal = -$bestCoupon->adjustmentTotal;
            }

            $coupons = collect_to_array($coupons);
        } else {
            $coupons = [];
        }
        return array($coupons, $bestCouponID, $bestCouponAdjustmentTotal);
    }

    /**
     * @param $user
     * @param $order
     * @return mixed
     */
    private function getOrderPoint($order, $user)
    {

        if ($order->type == Order::TYPE_SUIT && !settings('point_suit_enabled')) { //如果是套餐订单不能使用促销活动和优惠券。
            return [];
        }

        if ($order->type == Order::TYPE_SECKILL) { //如果是秒杀不能使用促销活动和优惠券。
            $seckill = $this->seckillItemRepository->getSeckillItemByID(request('seckill_item_id'));
            if ($seckill And !$seckill->use_point) {
                return [];
            };
        }

        if ($order->type == Order::TYPE_GROUPON) { //如果是拼团不能使用促销活动和优惠券。
            $groupon = $this->grouponItemRepository->getGrouponItemByID(request('groupon_item_id'));
            if ($groupon And !$groupon->use_point) {
                return [];
            };
        }

        if ($order->type == Order::TYPE_FREE_EVENT) { //如果是免费活动订单不能使用促销活动和优惠券。
            return [];
        }

        $orderPoint = [];

        if ($this->getCanUsePoint()) {
            $total = $this->getCanUsePointSum($order);
            $orderPoint['userPoint'] = $this->pointRepository->getSumPointValid($user->id, 'default'); //用户可用积分
            $orderPoint['pointToMoney'] = settings('point_proportion') ? settings('point_proportion') : 0;  //pointToMoney
            $orderPoint['pointLimit'] = settings('point_order_limit') / 100; //pointLimit
            $pointAmount = min($orderPoint['userPoint'] * $orderPoint['pointToMoney'], $total * $orderPoint['pointLimit']);
            $orderPoint['pointAmount'] = -$pointAmount;
            $orderPoint['pointCanUse'] = $pointAmount / $orderPoint['pointToMoney'];
            $orderPoint['pointCanotUseAmount'] = $this->getCanUsePointSum($order, false);
        }
        return $orderPoint;
    }

    /**
     * @param $cartItems
     * @param $order
     * @return OrderItem
     */
    private function BuildOrderItemsFromCartItems($cartItems, $order)
    {
        foreach ($cartItems as $key => $item) {
            if ($item->qty > 0) {

                $item_meta = [
                    /*'image' => $item->model->photo_url,*/
                    'image' => $item->img,
                    'detail_id' => $item->model->detail_id
                ];

                if (isset($item->dynamic_sku['id'])) {
                    $item_meta['dynamic_sku'] = $item->dynamic_sku;
                } else {
                    $item_meta['specs_text'] = $item->model->specs_text;
                }

                $singleDiscount = $this->discountService->getSingleDiscountByGoods($item->model);

                $orderItem = new OrderItem(['quantity' => $item->qty, 'unit_price' => $this->discountService->getProductPriceFromSingleDiscount($item->model, $singleDiscount),
                    'item_id' => $item->id, 'type' => $item->__model, 'item_name' => $item->name, 'item_meta' => $item_meta
                ]);

                if ($order->type == Order::TYPE_IN_SOURCE) { //内购的话设置为商品吊牌价
                    $orderItem->unit_price = $item->model->market_price;
                }

                if ($order->type == Order::TYPE_SUIT || $order->type == Order::TYPE_SECKILL || $order->type == Order::TYPE_GROUPON || $order->type == Order::TYPE_MULTI_GROUPON) { //套餐或秒杀或拼团设置为商品价格
                    $orderItem->unit_price = $item->price;
                    $orderItem->units_total = $item->price * $item->qty;
                }

                if ($order->type == Order::TYPE_FREE_EVENT) { //如果是免费活动订单，价格为0
                    $orderItem->unit_price = 0;
                    $orderItem->units_total = 0;
                }

                $orderItem->recalculateUnitsTotal();

                $order->addItem($orderItem);
            }
        }
        return $order;
    }

    /**
     * @return mixed
     */
    private function getCanUsePoint()
    {
        if (settings('order_use_point_enabled') && settings('point_order_limit') && settings('point_enabled') && settings('point_proportion')) {
            return true;
        }

        return false;
    }

    /**
     * 获取能使用\不能使用积分抵扣的金额
     * @param $order
     * @param $type
     * @return mixed
     */
    private function getCanUsePointSum($order, $type = true)
    {
        $items = $order->items;
        $filters = $items->filter(function ($item, $key) use ($type) {
            $goods = Goods::find($item->getItemId());
            if ($type) {
                return $goods->hasOnePoint->can_use_point;
            } else {
                return !$goods->hasOnePoint->can_use_point;
            }
        });
        return $filters->sum('total');
    }

}
