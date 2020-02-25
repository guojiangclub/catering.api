<?php

namespace ElementVip\Server\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use GuoJiangClub\Catering\Component\Order\Models\Order;
use ElementVip\Server\Models\OfflineOrder;
use Prettus\Repository\Traits\CacheableRepository;

class OrderRepository extends BaseRepository
{
    //use CacheableRepository;

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
     * 根据订单编号获得订单数据
     * @param $no
     * @return mixed
     */
    public function getOrderByNo($no)
    {
        return $this->with(['items', 'shippings', 'invoices', 'specialTypes'])->findByField('order_no', $no)->first();
    }

    public function getOrderOffilineByNo($no)
    {
        return OfflineOrder::with('goods')->where('order_no', $no)->first();
    }

    /**
     * 根据状态查询订单数据
     * @param $input
     * @param $user_id
     * @return mixed
     */
    public function getOrderByStatus($input, $user_id)
    {

        $order = $this->orderBy('created_at', 'desc')->with('items')->scopeQuery(function ($query) use ($input, $user_id) {
            $query = $query->where(['user_id' => $user_id]);
            if (isset($input['status']) && $input['status'] != 0) {
                $query->where(['status' => $input['status']]);
            } else {
                $query->where('status', '>', 0);
            }
            if (isset($input['type'])) {
                $query->where(['type' => $input['type']]);
            }


            return $query;
        })->paginate(15);

        return $order;

    }

    public function getOrdersByConditions($orderConditions, $itemConditions, $limit = 15, $withs = ['items'], $offline = 0, $type = [0, 1])
    {
        if ($offline != 1) {
            //线上订单
            $this->applyConditions($orderConditions);
            foreach ($withs as $with) {
                $this->with($with);
            }
            return $this->orderBy('created_at', 'desc')->scopeQuery(function ($query) use ($itemConditions, $type) {
                if (is_array($type)) {
                    $query = $query->whereIn('type', $type);
                } else {
                    $query = $query->where('type', $type);
                }
                if (count($itemConditions) > 0) {
                    $query = $query->whereHas('items', function ($query) use ($itemConditions) {
                        foreach ($itemConditions as $field => $value) {
                            if (is_array($value)) {
                                list($field, $condition, $val) = $value;
                                $query = $query->where($field, $condition, $val);
                            } else {
                                $query = $query->where($field, '=', $value);
                            }
                        }
                        return $query;
                    });
                }
                return $query;
            })->paginate($limit);

        } else {
            //线下订单
            return OfflineOrder::where(function ($query) use ($orderConditions) {
                foreach ($orderConditions as $field => $value) {
                    if (is_array($value)) {
                        list($field, $condition, $val) = $value;
                        $query = $query->where($field, $condition, $val);
                    } else {
                        $query = $query->where($field, '=', $value);
                    }
                }
            })->whereHas('goods', function ($query) use ($itemConditions) {
                foreach ($itemConditions as $field => $value) {
                    if ($field == 'item_id') {
                        $field = 'goods_id';
                    }
                    if (is_array($value)) {
                        list($field, $condition, $val) = $value;
                        if ($field == 'item_name') {
                            $field = 'goods_name';
                        }
                        $query = $query->where($field, $condition, $val);
                    } else {
                        $query = $query->where($field, '=', $value);
                    }
                }
                return $query;
            })->with('goods')->paginate($limit);
        }
    }

    public function getOrdersByCriteria($andConditions, $orConditions, $limit = 15, $offline = 0, $type = [0, 1])
    {
        if ($offline != 1) {
            //线上订单
            $orderItemTable = 'el_order_item';
            $query = $this->model->join($orderItemTable, $this->model->getQualifiedKeyName(), '=', $orderItemTable . '.order_id');

            if (is_array($type)) {
                $query = $query->whereIn($this->model->getTable() . '.' . 'type', $type);
            } else {
                $query = $query->where($this->model->getTable() . '.' . 'type', $type);
            }

            foreach ($andConditions as $field => $value) {
                if (is_array($value)) {
                    list($field, $condition, $val) = $value;
                    $query = $query->where($this->model->getTable() . '.' . $field, $condition, $val);
                } else {
                    $query = $query->where($this->model->getTable() . '.' . $field, '=', $value);
                }
            }

            $ids = $query->where(function ($query) use ($orConditions) {
                $index = 1;
                foreach ($orConditions as $field => $value) {
                    if (is_array($value)) {
                        list($field, $condition, $val) = $value;
                        if ($index == 1) {
                            $query = $query->where($field, $condition, $val);
                        } else {
                            $query = $query->orWhere($field, $condition, $val);
                        }
                    } else {
                        if ($index == 1) {
                            $query = $query->where($field, '=', $value);
                        } else {
                            $query = $query->orWhere($field, '=', $value);
                        }
                    }
                    $index++;
                }
            })->select($this->model->getTable() . '.*')->get()->pluck('id');

            return $this->orderBy('created_at', 'desc')->with('items')->scopeQuery(function ($query) use ($ids) {
                return $query->whereIn('id', $ids);
            })->paginate($limit);

        } else {
            //线下订单
            return OfflineOrder::where(function ($query) use ($andConditions) {
                foreach ($andConditions as $field => $value) {
                    if (is_array($value)) {
                        list($field, $condition, $val) = $value;
                        $query = $query->where($field, $condition, $val);
                    } else {
                        $query = $query->where($field, '=', $value);
                    }
                }
            })->whereHas('goods', function ($query) use ($orConditions) {
                $index = 1;
                foreach ($orConditions as $field => $value) {
                    if (is_array($value)) {
                        list($field, $condition, $val) = $value;
                        if ($field == 'item_id') {
                            $field = 'goods_id';
                        }
                        if ($field == 'item_name') {
                            $field = 'goods_name';
                        }
                        if ($index == 1) {
                            $query = $query->where($field, $condition, $val);
                        } else {
                            $query = $query->orWhere($field, $condition, $val);
                        }
                    } else {
                        if ($index == 1) {
                            $query = $query->where($field, '=', $value);
                        } else {
                            $query = $query->orWhere($field, '=', $value);
                        }
                    }
                    $index++;
                }
                return $query;
            })->with('goods')->paginate($limit);
        }
    }

    /**
     * 根据状态和用户获取订单的数量
     * @param $user_id
     * @param $status
     * @return mixed
     */
    public function getOrderCountByUserAndStatus($user_id, $status)
    {

        return $this->model->where('user_id', $user_id)->where('status', $status)->count();

    }


    /**
     * @param $goods_id_arr
     * @param $not_status
     * @param int $limit
     * @return array
     */
    public function getPurchasedUsersAvatar($goods_id_arr, $not_status)
    {

        $orders = $this->model->whereHas('items.product', function ($query) use ($goods_id_arr, $not_status) {

            return $query->whereIn('goods_id', $goods_id_arr);

        })->whereNotIn('status', $not_status)->with('user')->orderBy('created_at', 'desc')->get();


        $avatar = [];

        if (count($orders) > 0) {
            foreach ($orders as $key => $item) {
                if(isset($item->user->avatar) AND !empty($item->user->avatar)){
                    $avatar[] = $item->user->avatar;
                }

            }
        }

        return $avatar;
    }


    /**
     * @param $order_no
     * @return \Illuminate\Support\Collection|null
     */
    public function getShareOrderByNo($order_no)
    {


        $share_order = [];

        $goods_id_arr = [];

        $items=[];

        $order = $this->with('items')->with('user')->findByField('order_no', $order_no)->first();

        if ($order) {
            $share_order['id'] = $order->id;
            $share_order['status'] = $order->status;
            $share_order['user_id'] = $order->user_id;
            $share_order['accept_name'] = isset($order->user->nick_name)?$order->user->nick_name:'';
            $share_order['mobile'] = substr_replace($order->mobile, '****', 3, 4);
            $share_order['submit_time'] = $order->submit_time;
            $share_order['count'] = $order->count;
            $share_order['total_yuan'] = $order->total_yuan;
            $share_order['pay_time'] = $order->pay_time;
            if (count($order->items) > 0) {
                foreach ($order->items as $key=>$item) {
                    $items[$key]['item_name']=$item->item_name;
                    $items[$key]['item_meta']=$item->item_meta;
                    $items[$key]['quantity']=$item->quantity;
                    $items[$key]['units_total_yuan']=$item->units_total_yuan;
                    $goods_id_arr[] = $item->item_meta['detail_id'];
                }
            }
            $share_order['avatar'] = isset($order->user->avatar)?$order->user->avatar:'';
            $share_order['goods_id_arr'] = $goods_id_arr;
            $share_order['items'] = $items;
        }

        return count($share_order)?collect($share_order):null;

    }

}
