<?php

namespace GuoJiangClub\Catering\Server\Http\Controllers;

use Carbon\Carbon;
use Cart;
use Dingo\Api\Http\Response;
use GuoJiangClub\Catering\Component\Address\Models\Address;
use GuoJiangClub\Catering\Component\Discount\Applicators\DiscountApplicator;
use GuoJiangClub\Catering\Component\Discount\Models\Coupon;
use GuoJiangClub\Catering\Component\Discount\Models\Discount;
use GuoJiangClub\Catering\Component\Discount\Services\DiscountService;
use GuoJiangClub\Catering\Component\Point\Repository\PointRepository;
use GuoJiangClub\Catering\Component\Point\Applicator\PointApplicator;
use GuoJiangClub\Catering\Component\Order\Models\Comment;
use GuoJiangClub\Catering\Component\Order\Models\Order;
use GuoJiangClub\Catering\Component\Order\Models\OrderItem;
use GuoJiangClub\Catering\Component\Order\Processor\OrderProcessor;
use GuoJiangClub\Catering\Component\Order\Repositories\OrderRepository;
use GuoJiangClub\Catering\Component\Product\Models\Goods;
use GuoJiangClub\Catering\Component\Product\Models\Product;
use GuoJiangClub\Catering\Component\Product\Repositories\GoodsRepository;
use GuoJiangClub\Catering\Component\Product\Repositories\ProductRepository;
use GuoJiangClub\Catering\Component\Discount\Repositories\CouponRepository;
use GuoJiangClub\Catering\Component\Refund\Models\Refund;
use Illuminate\Support\Collection;
use Illuminate\Events\Dispatcher;
use DB;

class ShoppingController extends Controller
{
    private $goodsRepository;
    private $productRepository;
    private $discountService;
    private $orderRepository;
    private $discountApplicator;
    private $couponRepository;
    private $orderProcessor;
    private $pointRepository;
    private $pointApplicator;
    private $event;

    public function __construct(GoodsRepository $goodsRepository
        , ProductRepository $productRepository
        , DiscountService $discountService
        , OrderRepository $orderRepository
        , CouponRepository $couponRepository
        , DiscountApplicator $discountApplicator
        , OrderProcessor $orderProcessor
        , PointRepository $pointRepository
        , PointApplicator $pointApplicator
        , Dispatcher $event
    )
    {
        $this->goodsRepository    = $goodsRepository;
        $this->productRepository  = $productRepository;
        $this->discountService    = $discountService;
        $this->orderRepository    = $orderRepository;
        $this->discountApplicator = $discountApplicator;
        $this->couponRepository   = $couponRepository;
        $this->orderProcessor     = $orderProcessor;
        $this->pointRepository    = $pointRepository;
        $this->pointApplicator    = $pointApplicator;
        $this->event              = $event;
    }

    /**
     * 积分商城积分订单checkout方法
     */
    public function checkoutPoint()
    {
        try {
            $user = request()->user();

            if (!$user->mobile) {
                return $this->failed('您还没有绑定手机号');
            }

            if (empty(request('goods_id')) AND empty(request('product_id'))) {
                return $this->failed('请求错误，请选择商品');
            }

            $order = new Order(['user_id' => $user->id, 'type' => Order::TYPE_POINT, 'channel' => 'integral']);

            if (request('goods_id')) {
                $model = Goods::find(request('goods_id'));
            } else {
                $model = Product::find(request('product_id'));
            }

            if (!$model) {
                return $this->failed('商品不存在');
            }

            $quantity = request('quantity') ? request('quantity') : 1;

            if (!$model->getIsInSale($quantity)) {
                return $this->failed('库存不够');
            }

            if (!$model->is_largess) {
                return $this->failed('非积分兑换商品');
            }

            $userPoint = $this->pointRepository->getSumPointValid($user->id, 'default');

            if ($model->redeem_point > $userPoint) {
                return $this->failed('积分不够，无法兑换此商品');
            }

            $order->redeem_point = $model->redeem_point * $quantity;

            $item_meta = [
                'image'     => $model->photo_url,
                'detail_id' => $model->detail_id,
            ];

            $item_meta['specs_text'] = $model->specs_text;

            $orderItem = new OrderItem(['quantity' => $quantity, 'unit_price' => $model->sell_price,
                                        'item_id'  => $model->id, 'type' => get_class($model), 'item_name' => $model->name, 'item_meta' => $item_meta,
            ]);

            $orderItem->recalculateUnitsTotal();

            $order->addItem($orderItem);

            $defaultAddress = Address::getDefaultAddress(request()->user()->id);

            if (!$order = $this->orderProcessor->create($order)) {
                return $this->failed('订单提交失败，请确认后重试');
            }

            $discounts = [];
            $coupons   = [];

            return $this->success([
                'order'     => $order,
                'discounts' => $discounts,
                'coupons'   => $coupons,
                'address'   => $defaultAddress,
            ]);
        } catch (\Exception $exception) {
            return $this->failed($exception->getMessage());
        }
    }

    public function confirmPoint()
    {
        $user = request()->user();

        //TODO: 1. need to confirm whether the products are still in stock
        $order_no = request('order_no');
        if (!$order_no || !$order = $this->orderRepository->getOrderByNo($order_no)) {
            return $this->failed('订单不存在');
        }

        if ($user->cant('submit', $order)) {
            return $this->failed('You have no right to submit this order.');
        }

        if ($note = request('note')) {
            $order->note = $note;
        }

        foreach ($order->getItems() as $item) {
            $model = $item->type;
            $model = new $model();

            $product = $model->find($item->item_id);

            if (!$product->getIsInSale($item->quantity)) {
                return $this->failed('商品库存不够，请重新下单');
            }

            if (!$product->is_largess) {
                return $this->failed('非积分兑换商品');
            }
        }

        try {

            DB::beginTransaction();

            $userPoint = $this->pointRepository->getSumPointValid($user->id, 'default');

            if ($userPoint < $order->redeem_point) {
                return $this->failed('积分不够');
            }

            $point = $this->pointRepository->create([
                'user_id'    => $order->user_id,
                'action'     => 'order_point',
                'note'       => '积分订单',
                'value'      => (-1) * $order->redeem_point,
                'valid_time' => 0,
                'item_type'  => Order::class,
                'item_id'    => $order->id,
            ]);

            event('point.change', $user->id);

            //5. 保存收获地址信息。
            if ($address = Address::find(request('address_id'))) {
                $order->accept_name  = $address->accept_name;
                $order->mobile       = $address->mobile;
                $order->address      = $address->address;
                $order->address_name = $address->address_name;
            }

            event('st.wechat.message.point', [$user, '购物消耗积分', $order->redeem_point]);

            //5. 保存订单状态
            $order->status      = Order::STATUS_PAY;
            $order->submit_time = Carbon::now();
            $order->pay_time    = Carbon::now();
            $order->pay_status  = 1;
            $order->source      = request('source');
            $order->save();

            //6. remove goods store.
            foreach ($order->getItems() as $item) {
                $model   = $item->type;
                $model   = new $model();
                $product = $model->find($item->item_id);
                $product->reduceStock($item->quantity);
                $product->increaseSales($item->quantity);
                $product->save();
            }

            event('order.customer.paid', [$order]);

            DB::commit();

            return $this->success(['order' => $order]);
        } catch (\Exception $exception) {
            DB::rollBack();

            \Log::info($exception->getMessage() . $exception->getTraceAsString());

            return $this->failed('提交订单失败');
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
            return $this->failed('订单不存在');
        }

        if ($user->cant('cancel', $order)) {
            return $this->failed('You have no right to cancel this order.');
        }

        $this->orderProcessor->cancel($order);

        //TODO: 用户未付款前取消订单后，需要还原库存
        foreach ($order->getItems() as $item) {
            $model   = $item->type;
            $model   = new $model();
            $product = $model->find($item->item_id);
            $product->restoreStock($item->quantity);
            $product->restoreSales($item->quantity);
            $product->save();
        }

        return $this->success();
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
                return $this->failed('订单不存在');
            }

            if ($user->cant('received', $order)) {
                return $this->failed('You have no right to received this order.');
            }

            $this->orderProcessor->received($order);

            DB::commit();

            return $this->success();
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
            return $this->failed('订单不存在');
        }

        if ($user->cant('delete', $order)) {
            return $this->failed('You have no right to delete this order.');
        }

        $this->orderProcessor->delete($order);

        return $this->success();
    }

    public function review()
    {
        $user     = request()->user();
        $comments = request()->except('_token');

        if (!is_array($comments)) {
            return $this->failed('提交参数错误');
        }

        foreach ($comments as $key => $comment) {

            if (!isset($comment['order_no']) OR !$order = $this->orderRepository->getOrderByNo($comment['order_no'])) {
                return $this->failed('订单 ' . $comment['order_no'] . ' 不存在');
            }

            if (!isset($comment['order_item_id']) OR !$orderItem = OrderItem::find($comment['order_item_id'])) {
                return $this->failed('You need to pass into specific order_item_id');
            }

            if ($user->cant('review', [$order, $orderItem])) {
                return $this->failed('You have no right to review this order.');
            }

            if ($order->comments()->where('order_item_id', $comment['order_item_id'])->count() > 0) {
                return $this->failed('该产品已经评论，无法再次评论');
            }

            $content  = isset($comment['contents']) ? $comment['contents'] : '';
            $point    = isset($comment['point']) ? $comment['point'] : 5;
            $pic_list = isset($comment['images']) ? $comment['images'] : [];

            $comment = new Comment(['user_id'    => $user->id, 'order_item_id' => $comment['order_item_id']
                                    , 'item_id'  => $orderItem->item_id, 'item_meta' => $orderItem->item_meta
                                    , 'contents' => $content, 'point' => $point
                                    , 'status'   => settings('') == 'show' ? 'show' : 'hidden'
                                    , 'pic_list' => $pic_list
                                    , 'goods_id' => $orderItem->item_meta['detail_id'],
            ]);

            $order->comments()->save($comment);

            event('order.item.commented', $orderItem);
            event('product.commented', [$orderItem, $point]);

            $this->orderProcessor->process($order);
        }

        return $this->success();
    }

    /**
     * @return \Illuminate\Support\Collection
     * @throws \Exception
     */
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
        } else {
            $cartItems = Cart::all();
        }

        return $cartItems;
    }

    /**
     * call by call_user_func()
     *
     * @return bool|Collection
     * @throws \Exception
     */
    private function getSelectedItemFromCart()
    {
        //获取购物车中选中的商品数据

        $ids = request('cart_ids');

        if (!$ids || count($ids) == 0) {
            return false;
        }

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
        }

        return $cartItems;
    }

    private function checkStock($cartItems)
    {
        $flag = true;

        if (count($cartItems) == 0) {
            return false;
        }

        foreach ($cartItems as $key => $item) {
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

    public function extraInfo()
    {
        $user         = request()->user();
        $point        = $this->pointRepository->getSumPointValid($user->id, 'default');
        $pointToMoney = settings('point_proportion') ? settings('point_proportion') : 0;
        $pointLimit   = settings('point_order_limit') ? settings('point_order_limit') : 1;

        return $this->success([
            'userPoint'    => $point,
            'pointToMoney' => $pointToMoney / 1,
            'pointLimit'   => $pointLimit / 100,
        ]);
    }
}
