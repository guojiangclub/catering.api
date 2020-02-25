<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016-09-27
 * Time: 17:42
 */

namespace ElementVip\Server\Transformers;

use Carbon\Carbon;
use GuoJiangClub\Catering\Component\Order\Models\Order;
use ElementVip\Component\Order\Models\SpecialType;
use ElementVip\Component\Shipping\Models\Shipping;
use iBrand\Component\MultiGroupon\Models\MultiGrouponUsers;

class OrdersTransformer extends BaseTransformer
{
    protected $type;

    public function __construct($type = 'detail')
    {
        $this->type = $type;
    }

    public function transformData($model)
    {
        $model->from = '官网商城';
        switch ($model->type) {
            case 2:
                $model->type_text = '内购订单';
                break;
            case 4:
                $model->type_text = '套餐订单';
                break;
            case 7:
                $model->type_text = '秒杀订单';
                break;
            default:
                $model->type_text = '普通订单';
                break;
        }

        $model->adjustment_point = 0;
        if ($model->getTable() == 'el_order' AND $point = $model->adjustments->where('origin_type', 'point')->first() AND settings('point_proportion')) {
            $model->adjustment_point = -$point->amount / settings('point_proportion');
        }

        if ($model->pay_status == 0) {
            $close_time_limit = settings('order_auto_cancel_time') ? settings('order_auto_cancel_time') * 60 : 1440 * 60;
            $create_time = strtotime($model->created_at);
            $model->will_closed_at = date('Y-m-d H:i:s', $create_time + $close_time_limit);
        }

        if ($model->distribution_status == 2 AND $model->status == 2) {
            $model->status = 31; //部分发货
        }

        /*获取item的售后信息*/
        foreach ($model->items as $item) {
            $refund_btn = [];
            foreach ($item->refunds as $value) {
                $refund_btn[] = [
                    'refund_no' => $value->refund_no,
                    'refund_status' => $value->status,
                    'refund_status_text' => $value->status_text
                ];
            }
            $item->refund_btn = $refund_btn;
        }

        if ($this->type == 'detail') {
            $groupedItem = $model->items->groupBy('shipping_id');
            $model->group_item_count = count($groupedItem);
            $groupOrderItem = [];
            if (count($groupedItem) > 1) {
                $groupedItem = $groupedItem->toArray();
                $i = 0;
                foreach ($groupedItem as $key => $item) {
                    $groupOrderItem[$key]['item'] = $item;
                    if ($key) {
                        $i++;
                        $shipping = Shipping::find($key);
                        $groupOrderItem[$key]['shipping'] = $shipping->toArray();
                        $groupOrderItem[$key]['shipping_title'] = '发货单' . $i;
                    }
                }

                $model->group_order_item = array_values($groupOrderItem);
            }

            if ($model->type == Order::TYPE_MULTI_GROUPON AND
                $currentUser = MultiGrouponUsers::where('order_id', $model->id)->where('user_id', $model->user_id)->first()
            ) { //如果是拼团订单
                $model->multi_groupon_users = MultiGrouponUsers::where('multi_groupon_items_id', $currentUser->multi_groupon_items_id)->where('status', 1)->get();
                $model->multi_groupon_status = $currentUser->grouponItem->status;
            }

        }

        //判断订单能否进行售后
        if ($this->type == 'refund' OR $this->type == 'detail') {
            $days = settings('order_can_refund_day') ? settings('order_can_refund_day') : 7;
            $model->can_refund = true;
            if (!in_array($model->status, [2, 3, 4, 31])) {
                $model->can_refund = false;
            } elseif ($model->accept_time AND strtotime($model->accept_time) < Carbon::now()->addDay(-$days)->timestamp) {
                $model->can_refund = false;
            } else {
                $refunds = $model->refunds->filter(function ($item) {
                    return !in_array($item->status, [2, 4]);
                });
                //如果进行中或者已完成的售后次数等于订单item数量
                if (count($refunds) == $model->countItems()) {
                    $model->can_refund = false;
                }

                //如果所有item进行2次售后申请
                if ($model->refunds->count() == $model->countItems() * 2) {
                    $model->can_refund = false;
                }
            }
        }

        $goods = $model->toArray();
        return $goods;
    }

}