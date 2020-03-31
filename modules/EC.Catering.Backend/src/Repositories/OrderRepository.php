<?php

namespace GuoJiangClub\EC\Catering\Backend\Repositories;

use GuoJiangClub\Catering\Component\Payment\Models\Payment;
use GuoJiangClub\Catering\Component\Point\Model\Point;
use GuoJiangClub\EC\Catering\Backend\Models\OrderItem;
use GuoJiangClub\EC\Catering\Backend\Models\Product;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use GuoJiangClub\EC\Catering\Backend\Models\Order;
use GuoJiangClub\EC\Catering\Backend\Exceptions\GeneralException;
use GuoJiangClub\Catering\Component\Discount\Models\Coupon;
use GuoJiangClub\EC\Catering\Backend\Models\ShippingMethod;
use DB;

/**
 * Class OrderRepositoryEloquent
 *
 * @package namespace App\Repositories;
 */
class OrderRepository extends BaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Order::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    /**
     * 根据订单编号获得订单数据
     *
     * @return mixed
     */
    public function getOrderByNo($no)
    {
        return $this->findByField('order_no', $no)->first();
    }

    /**
     * 根据订单编号更新数据
     *
     * @param array $attributes
     * @param       $no
     *
     * @return mixed
     */
    public function updateByOrderNo(array $attributes, $no)
    {
        $order = $this->findByField('order_no', $no)->first();
        $order->fill($attributes);
        $order->save();

        return $order;
    }

    /**
     * 根据订单状态，用户ID获取用户订单
     *
     * @param $uid
     * @param $status
     *
     * @return mixed
     */
    public function getOrdersByStatus($uid, $status)
    {
        if ($status) {

            return $this->orderBy('updated_at', 'desc')->findWhere(['user_id' => $uid, 'status' => $status]);
        } else {

            return $this->orderBy('updated_at', 'desc')->findWhere(['user_id' => $uid, ['status', '>', '0']]);
        }
    }

    /**
     * @param     $where
     * @param int $limit
     * @param     $time
     *
     * @return mixed
     */

    public function getOrdersPaginated($where, $limit = 50, $time = [])
    {
        $date = $this->scopeQuery(function ($query) use ($where, $time) {
            if (is_array($where)) {
                foreach ($where as $key => $value) {
                    if (is_array($value)) {
                        list($operate, $va) = $value;

                        if ($key == 'address') {
                            $query = $query->where(function ($query) use ($key, $operate, $va) {
                                $query->where($key, $operate, $va)->orWhere('address_name', $operate, $va);
                            });
                        } else {
                            $query = $query->where($key, $operate, $va);
                        }
                    } elseif ($value == 'all') {
                        $query = $query->whereBetween($key, [1, 6]);
                    } else {
                        $query = $query->where($key, $value);
                    }
                }
            }

            if (is_array($time)) {
                foreach ($time as $key => $value) {
                    if (is_array($value)) {
                        list($operate, $va) = $value;
                        $query = $query->where($key, $operate, $va);
                    } else {
                        $query = $query->where($key, $value);
                    }
                }
            }

            return $query->orderBy('updated_at', 'desc');
        });

        if ($limit == 0) {
            return $date->all();
        } else {
            return $date->paginate($limit);
        }
    }

    /**
     * @param $id
     *
     * @return mixed
     * @throws GeneralException
     */

    public function findOrThrowException($id)
    {

        $order = Order::withTrashed()->find($id);

        if (is_null($order)) {

            throw new GeneralException('订单不存在');
        }

        return $order;
    }


    /**
     * @param $id
     *
     * @return mixed
     */
// 获得运输方式
    public function getShippingMethod($order)
    {
        $shipping = null;
        if ($order->status < 6 AND 2 < $order->status AND $shipping = $order->shipping()->first()) {
            $ShippingMethod        = ShippingMethod::find($shipping->method_id);
            $shipping->method_name = $ShippingMethod->name;
        }

        return $shipping;
    }

    /**
     * @param $id
     *
     * @return bool
     * @throws GeneralException
     */
    public function destroy($id)
    {
        $order = $this->findOrThrowException($id);
        if ($order->delete()) {
            return true;
        }

        throw new GeneralException("删除失败，请重试!");
    }

    /**
     * @param array $conditions
     *
     * @return mixed
     */

    public function orderSearch($conditions = [])
    {

        $con    = isset($conditions['conditions']) ? $conditions['conditions'] : '';
        $value  = isset($conditions['search']) ? $conditions['search'] : '';
        $status = isset($conditions['status']) ? $conditions['status'] : 0;

        return $this->getOrdersPaginated(1, $status, $con, $value);
    }


//     更新发货状态

    /**
     * @param $order_id
     * @param $status
     * @param $send_time
     * @param $shipping_id ;
     *
     * @return mixed
     */

    public function updateDeliveryStatus($order_id, $status, $send_time, $shipping_id)
    {
        return $this->update(['status'              => $status,
                              'distribution_status' => 1,
                              'send_time'           => $send_time,
                              'distribution'        => $shipping_id,
        ], $order_id);
    }

    public function getExportOrders($where, $limit = 50, $time = [])
    {
        $data = Order::where(function ($query) use ($where, $time) {
            if (is_array($where) && count($where)) {
                foreach ($where as $key => $value) {
                    if ($key != 'sku') {
                        if (is_array($value)) {
                            list($operate, $va) = $value;
                            $query = $query->where($key, $operate, $va);
                        } else {
                            $query = $query->where($key, $value);
                        }
                    }
                }
            }
            if (is_array($time)) {
                foreach ($time as $key => $value) {
                    if (is_array($value)) {
                        list($operate, $va) = $value;
                        $query = $query->where($key, $operate, $va);
                    } else {
                        $query = $query->where($key, $value);
                    }
                }
            }

//            $query =$query->where(['status', '>', '0']);
            return $query;
        });

        if (isset($where['sku'])) {
            if (is_array($where['sku'])) {
                list($operate, $va) = $where['sku'];
                $products = Product::where('sku', $operate, $va)->pluck('id')->toArray();
            } else {
                $products = Product::where('sku', $where['sku'])->pluck('id')->toArray();
            }
            $order_ids = OrderItem::where('type', 'GuoJiangClub\Catering\Component\Product\Models\Product')
                ->whereIn('item_id', $products)->pluck('order_id')->toArray();
            $data      = $data->whereIn('id', $order_ids);
        }

        if (is_array($ids = request('ids'))) {
            $data = $data->whereIn('id', $ids);
        }

        $data = $data->with('payment', 'adjustments')->orderBy('created_at', 'desc');
        if ($limit == 0) {
            return $data->get();
        } else {
            return $data->paginate($limit);
        }
    }

    public function getOrderAdjustments($order)
    {
        $adjustments = $order->adjustments()->get();
        foreach ($adjustments as $item) {
            $discount          = Coupon::find($item->origin_id);
            $item->coupon_code = isset($discount->code) ? $discount->code : '';
        }

        return $adjustments;
    }

    public function getOrderPoints($orderItems)
    {
        $item_id = [];
        $point   = [];
        foreach ($orderItems as $item) {
            $item_id[] = $item->id;
        }
        if (isset($item_id)) {
            $point = Point::where(['action' => 'order_item'])->whereIn('item_id', $item_id)->get();
        }

        return $point;
    }

    /**
     * 获取积分商城订单积分使用情况
     *
     * @param $order_id
     *
     * @return mixed
     */
    public function getPointMallOrderPoints($order_id)
    {
        $point = Point::where(['action' => 'order_point'])->where('item_id', $order_id)->get();

        return $point;
    }

    public function getOrderConsumePoint($order_id)
    {
        return Point::where('action', 'order_discount')->where('item_id', $order_id)->get();
    }

    /**
     * 20170417
     * @param       $where
     * @param int   $limit
     * @param array $time
     *
     * @return mixed
     */
    public function getExportOrdersData($where, $limit = 50, $time = [], $more = [], $pay_time = [])
    {
        $data = Order::where(function ($query) use ($where, $time, $pay_time, $more) {

            if (is_array($where) && count($where)) {
                foreach ($where as $key => $value) {
                    if ($key != 'sku' AND $key != 'spu') {
                        if (is_array($value)) {
                            list($operate, $va) = $value;
                            $query = $query->where($key, $operate, $va);
                        } else {
                            $query = $query->where($key, $value);
                        }
                    }
                }
            }
            if (is_array($time) AND count($time) > 0) {
                foreach ($time as $key => $value) {
                    if (is_array($value)) {
                        list($operate, $va) = $value;
                        $query = $query->where($key, $operate, $va);
                    } else {
                        $query = $query->where($key, $value);
                    }
                }
            }

            /*付款时间*/
            if (is_array($pay_time) AND count($pay_time) > 0) {
                foreach ($pay_time as $key => $value) {
                    if (is_array($value)) {
                        list($operate, $va) = $value;
                        $query = $query->where($key, $operate, $va);
                    } else {
                        $query = $query->where($key, $value);
                    }
                }
            }

            /*订单总金额*/
            if (is_array($more) AND isset($more['total'])) {
                $query = $query->whereBetween('total', $more['total']);
            }

            /*收货地址*/
            if (is_array($more) AND isset($more['address_name'])) {
                list($operate, $va) = $more['address_name'];
                $query = $query->where('address_name', $operate, $va);
            }

            if (isset($more['supplier']) AND $more['supplier']) {
                $query = $query->whereHas('items', function ($query) use ($where, $more) {
                    $query->whereIn('supplier_id', $more['supplier']);
                    if ($where['status'] == 2) {
                        $query->where('is_send', 0);
                    }
                });
            }

            return $query;
        });

        /*付款方式*/
        if (isset($more['pay_method'])) {
            $order_ids_pay = Payment::where('channel', $more['pay_method'])->pluck('order_id')->toArray();
            $data          = $data->whereIn('id', $order_ids_pay);
        }

        if (isset($where['spu'])) {
            list($operate, $va) = $where['spu'];
            $products          = DB::table(config('ibrand.app.database.prefix', 'ibrand_') . 'goods')
                ->where('goods_no', $operate, $va)
                ->join(config('ibrand.app.database.prefix', 'ibrand_') . 'goods_product', config('ibrand.app.database.prefix', 'ibrand_') . 'goods.id', '=', 'el_goods_product.goods_id')
                ->pluck(config('ibrand.app.database.prefix', 'ibrand_') . 'goods_product.id')->toArray();
            $order_ids_product = OrderItem::where('type', 'GuoJiangClub\Catering\Component\Product\Models\Product')
                ->whereIn('item_id', $products)->pluck('order_id')->toArray();

            $goods           = DB::table(config('ibrand.app.database.prefix', 'ibrand_') . 'goods')
                ->where('goods_no', $operate, $va)
                ->pluck('id')->toArray();
            $order_ids_goods = OrderItem::where('type', 'GuoJiangClub\Catering\Component\Product\Models\Goods')
                ->whereIn('item_id', $goods)->pluck('order_id')->toArray();

            $data = $data->whereIn('id', array_merge($order_ids_product, $order_ids_goods));
        }

        if (isset($where['sku'])) {
            if (is_array($where['sku'])) {
                list($operate, $va) = $where['sku'];
                $products = Product::where('sku', $operate, $va)->pluck('id')->toArray();
            } else {
                $products = Product::where('sku', $where['sku'])->pluck('id')->toArray();
            }
            $order_ids = OrderItem::where('type', 'GuoJiangClub\Catering\Component\Product\Models\Product')
                ->whereIn('item_id', $products)->pluck('order_id')->toArray();
            $data      = $data->whereIn('id', $order_ids);
        }

        if (is_array($ids = request('ids'))) {
            $data = $data->whereIn('id', $ids);
        }

        $data = $data->with('payment', 'adjustments', 'user', 'refunds', 'grouponUser')->orderBy('created_at', 'desc');

        if ($limit == 0) {
            return $data->get();
        } else {
            return $data->paginate($limit);
        }
    }

    /**
     * 获取拆分订单数据
     *
     * @param       $where
     * @param int   $limit
     * @param array $time
     * @param array $more
     * @param array $pay_time
     *
     * @return array
     */
    public function getSplitOrdersData($where, $limit = 50, $time = [], $more = [], $pay_time = [])
    {

        $users = $this->getSplitOrderUser($more, $limit);
        $data  = collect();
        if ($users->total() > 0) {
            $tmp      = $users->toArray();
            $users_id = array_column($tmp['data'], 'user_id');

            $data = Order::where(function ($query) use ($where, $time, $pay_time, $more, $users_id) {
                $query = $query->whereIn('user_id', $users_id);
                if (is_array($where) && count($where)) {
                    foreach ($where as $key => $value) {
                        if ($key != 'sku' AND $key != 'spu') {
                            if (is_array($value)) {
                                list($operate, $va) = $value;
                                $query = $query->where($key, $operate, $va);
                            } else {
                                $query = $query->where($key, $value);
                            }
                        }
                    }
                }
                if (is_array($time) AND count($time) > 0) {
                    foreach ($time as $key => $value) {
                        if (is_array($value)) {
                            list($operate, $va) = $value;
                            $query = $query->where($key, $operate, $va);
                        } else {
                            $query = $query->where($key, $value);
                        }
                    }
                }

                /*付款时间*/
                if (is_array($pay_time) AND count($pay_time) > 0) {
                    foreach ($pay_time as $key => $value) {
                        if (is_array($value)) {
                            list($operate, $va) = $value;
                            $query = $query->where($key, $operate, $va);
                        } else {
                            $query = $query->where($key, $value);
                        }
                    }
                }

                /*订单总金额*/
                if (is_array($more) AND isset($more['total'])) {
                    $query = $query->whereBetween('total', $more['total']);
                }

                /*收货地址*/
                if (is_array($more) AND isset($more['address_name'])) {
                    list($operate, $va) = $more['address_name'];
                    $query = $query->where('address_name', $operate, $va);
                }

                if (isset($more['supplier']) AND $more['supplier']) {
                    $query = $query->whereHas('items', function ($query) use ($where, $more) {
                        $query->whereIn('supplier_id', $more['supplier']);
                        if ($where['status'] == 2) {
                            $query->where('is_send', 0);
                        }
                    });
                }

                return $query;
            });

            /*付款方式*/
            if (isset($more['pay_method'])) {
                $order_ids_pay = Payment::where('channel', $more['pay_method'])->pluck('order_id')->toArray();
                $data          = $data->whereIn('id', $order_ids_pay);
            }

            if (isset($where['spu'])) {
                list($operate, $va) = $where['spu'];
                $products          = DB::table(config('ibrand.app.database.prefix', 'ibrand_') . 'goods')
                    ->where('goods_no', $operate, $va)
                    ->join(config('ibrand.app.database.prefix', 'ibrand_') . 'goods_product', 'el_goods.id', '=', 'el_goods_product.goods_id')
                    ->pluck(config('ibrand.app.database.prefix', 'ibrand_') . 'goods_product.id')->toArray();
                $order_ids_product = OrderItem::where('type', 'GuoJiangClub\Catering\Component\Product\Models\Product')
                    ->whereIn('item_id', $products)->pluck('order_id')->toArray();

                $goods           = DB::table(config('ibrand.app.database.prefix', 'ibrand_') . 'goods')
                    ->where('goods_no', $operate, $va)
                    ->pluck('id')->toArray();
                $order_ids_goods = OrderItem::where('type', 'GuoJiangClub\Catering\Component\Product\Models\Goods')
                    ->whereIn('item_id', $goods)->pluck('order_id')->toArray();

                $data = $data->whereIn('id', array_merge($order_ids_product, $order_ids_goods));
            }

            if (isset($where['sku'])) {
                if (is_array($where['sku'])) {
                    list($operate, $va) = $where['sku'];
                    $products = Product::where('sku', $operate, $va)->pluck('id')->toArray();
                } else {
                    $products = Product::where('sku', $where['sku'])->pluck('id')->toArray();
                }
                $order_ids = OrderItem::where('type', 'GuoJiangClub\Catering\Component\Product\Models\Product')
                    ->whereIn('item_id', $products)->pluck('order_id')->toArray();
                $data      = $data->whereIn('id', $order_ids);
            }

//            $data = $data->with('payment', 'adjustments')->orderBy('user_id')->orderBy('status', 'asc')->get();
            $data = $data->with('payment', 'adjustments', 'user', 'refunds', 'grouponUser')->orderBy('created_at', 'desc')->get();
        }

        return ['users' => $users, 'orders' => $data];
    }

    /**
     * 拆分订单，按照用户分页
     *
     * @param $more
     * @param $limit
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    protected function getSplitOrderUser($more, $limit)
    {
        $ids    = request('ids');
        $prefix = config('ibrand.app.database.prefix', 'ibrand_');

        if (isset($more['supplier']) AND $more['supplier']) {
            if ($ids) {
                $users = DB::table($prefix . 'order')
                    ->join($prefix . 'order_item', $prefix . 'order.id', '=', $prefix . 'order_item.order_id')
                    ->select($prefix . 'order.user_id')
                    ->where($prefix . 'order.status', 2)
                    ->where($prefix . 'order.pay_status', 1)
                    ->whereIn($prefix . 'order_item.supplier_id', $more['supplier'])
                    ->whereIn($prefix . 'order.user_id', $ids)
                    ->groupBy($prefix . 'order.user_id')
                    ->orderBy($prefix . 'order.user_id', 'desc')->paginate($limit);
            } else {
                $users = DB::table($prefix . 'order')
                    ->join($prefix . 'order_item', $prefix . 'order.id', '=', $prefix . 'order_item.order_id')
                    ->select($prefix . 'order.user_id')
                    ->where($prefix . 'order.status', 2)
                    ->where($prefix . 'order.pay_status', 1)
                    ->whereIn($prefix . 'order_item.supplier_id', $more['supplier'])
                    ->groupBy($prefix . 'order.user_id')
                    ->orderBy($prefix . 'order.user_id', 'desc')->paginate($limit);
            }
        } else {
            if ($ids) {
                $users = DB::table($prefix . 'order')
                    ->select('user_id')
                    ->where('status', 2)
                    ->where('pay_status', 1)
                    ->whereIn('user_id', $ids)
                    ->groupBy('user_id')
                    ->orderBy('user_id', 'desc')->paginate($limit);
            } else {
                $users = DB::table($prefix . 'order')
                    ->select('user_id')
                    ->where('status', 2)
                    ->where('pay_status', 1)
                    ->groupBy('user_id')
                    ->orderBy('user_id', 'desc')->paginate($limit);
            }
        }

        return $users;
    }
}
