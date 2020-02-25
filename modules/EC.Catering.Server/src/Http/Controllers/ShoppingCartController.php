<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/10/3
 * Time: 11:30
 */

namespace ElementVip\Server\Http\Controllers;

use Cart;
use ElementVip\Component\Discount\Services\DiscountService;
use ElementVip\Member\Backend\Models\Staff;
use phpDocumentor\Reflection\Element;
use ElementVip\Store\Backend\Repositories\GoodsUserLimitRepository;
use ElementVip\Store\Backend\Repositories\GoodsLimitRepository;
use Carbon\Carbon;

class ShoppingCartController extends Controller
{
    protected $discountService;
    protected $goodsUserLimit;
    protected $goodsLimit;

    public function __construct(DiscountService $discountService,
                                GoodsUserLimitRepository $goodsUserLimitRepository,
                                GoodsLimitRepository $goodsLimitRepository)
    {
        $this->discountService = $discountService;
        $this->goodsUserLimit = $goodsUserLimitRepository;
        $this->goodsLimit = $goodsLimitRepository;
    }

    public function index()
    {
        if (empty(request()->all())) {
            $carts = Cart::search(['channel' => 'normal']);
        } else {
            $carts = Cart::search(request()->all());
        }

        foreach ($carts as $item) {

            if ($item AND $item->model ){
                $item['stock_qty'] = $item->model->stock_qty;
            } else {
                $item['stock_qty'] = 0;
            }
        }

        return $this->api($carts);
    }

    public function store()
    {
        $carts = request()->all();

        if (count($carts) == 0) {
            return $this->response()->noContent();
        }

        //商品限购
        foreach ($carts as $value) {
            $goods_id = $value['attributes']['com_id'];
            $goods_limit = $this->goodsLimit->findWhere(['goods_id' => $goods_id, 'activity' => 1, ['starts_at', '<=', Carbon::now()], ['ends_at', '>=', Carbon::now()]])->first();
            if ($goods_limit) {
                $num = 0;
                $buy_num = $goods_limit->quantity;
                $items = Cart::all();
                foreach ($items as $item) {
                    if ($goods_id == $item->com_id) {
                        $num += $item->qty;
                    }
                }

                $check = $this->goodsUserLimit->findWhere(['user_id' => request()->user()->id, 'goods_id' => $goods_id])->first();
                if ($check) {
                    $buy_num = $goods_limit->quantity - $check->buy_nums;
                }

                $start_at = strtotime($goods_limit->starts_at);
                $end_at = strtotime($goods_limit->ends_at);
                if (time() >= $start_at && time() <= $end_at && ($value['qty'] + $num > $buy_num)) {

                    $message = '商品: ' . $value['name'] . ' 每人限购' . $goods_limit->quantity . '件';
                    if (isset($value['type']) && 'goods' == $value['type']) {
                        $message = '本商品每人限购' . $goods_limit->quantity . '件';
                    }

                    return $this->api(['type' => 'purchase'], false, 500, $message);
                }
            }
        }

        foreach ($carts as $cart) {

            if (!isset($cart['attributes']) OR (array_key_exists('bundle_id', $cart['attributes']) AND (count($cart['attributes']) == 1))) {
                Cart::associate('ElementVip\Component\Product\Models\Goods');
                $attributes = isset($cart['attributes']) ? $cart['attributes'] : [];
                $attributes['type'] = 'spu';
            } elseif (isset($cart['attributes']) AND isset($cart['attributes']['dynamic_sku'])) {
                Cart::associate('ElementVip\Component\Product\Models\Goods');
                $attributes = $cart['attributes'];
                $attributes['type'] = 'dynamic_sku';
            } elseif (isset($cart['attributes']) AND !isset($cart['attributes']['sku'])) {
                Cart::associate('ElementVip\Component\Product\Models\Goods');
                $attributes = isset($cart['attributes']) ? $cart['attributes'] : [];
                $attributes['type'] = 'spu';
            } else {
                Cart::associate('ElementVip\Component\Product\Models\Product');
                $attributes = $cart['attributes'];
                $attributes['type'] = 'sku';
            }

            if (!isset($cart['id'])) {
                continue;
            }

            $item = Cart::add($cart['id'], $cart['name'], $cart['qty'], $cart['price'], $attributes);

            if ($item AND $item->model) {
                if (!$item->model->getIsInSale($item->qty) || $item->model->is_del == 2) {
                    Cart::remove($item->rawId());
                    $qty = $this->getIsInSaleQty($item, $item->qty);
                    if ($qty != 0) {
                        Cart::add($cart['id'], $cart['name'], $qty, $cart['price'], $attributes);
                    }
                    return $this->api([], false, 500, '商品库存不足或已下架，请重新选择');
                }
            } else {
                \Log::info('添加购物车失败，数据：'.json_encode($cart));
                return $this->api([], false, 500, '添加失败');
            }

            Cart::update($item->rawId(), ['status' => 'online', 'market_price' => $item->model->market_price]);

            if ($item->channel AND $item->channel == 'employee') {
                if ($user = request()->user('api') AND
                    $userRole = $user->roles->first() AND
                    $userRole->name == 'employee' AND
                    $staff = Staff::where('mobile', $user->mobile)->first() AND
                    $staff->active_status == 1
                ) {
                    $ruleGoods = $this->discountService->getGoodsByRole('employee');
                    $percentageGroup = $ruleGoods['percentageGroup'];
                    if ($ruleGoods AND in_array($item->model->detail_id, $ruleGoods['spu'])) {
                        Cart::update($item->rawId(), ['price' => $item->getRolePrice($percentageGroup)]);
                    } else {
                        Cart::update($item->rawId(), ['channel' => 'normal']);
                    }
                }
            } else {
                Cart::update($item->rawId(), ['channel' => 'normal']);
            }
        }

        return $this->api(Cart::all());
    }

    public function update($id)
    {
        $item = Cart::get($id);

        if (!$item) {
            return $this->api([], false, 500, '购物车数据不存在');
        }

        $attributes = request('attributes');

        if ($attributes['qty'] <= $item->model->stock_qty) {
            $item = Cart::update($id, $attributes);
            return $this->api($item);
        } else {
            return $this->api(['stock_qty' => $item->model->stock_qty], false, 201, '库存不够');
        }
    }

    public function delete($id)
    {
        return $this->api(Cart::remove($id));
    }

    public function clear()
    {
        Cart::destroy();
        return $this->api(Cart::all());
    }

    /**
     * 获取购物车中所有商品的数量
     */
    public function count()
    {
        return $this->api(Cart::count());
    }

    public function getIsInSaleQty($item, $qty)
    {
        if ($qty <= 0)
            return 0;
        if ($item->model->getIsInSale($qty)) {
            return $qty;
        } else {
            return $this->getIsInSaleQty($item, $qty - 1);
        }
    }
}