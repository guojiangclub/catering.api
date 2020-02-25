<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/3/15
 * Time: 21:53
 */

namespace ElementVip\Server\Transformers;


class OrderItemTransformer extends BaseTransformer
{
    public function transformData($model)
    {
        $model->distribution_status = $model->order->distribution_status;

        if ($model->order->distribution_status == 1 AND $model->is_send == 0) {
            $model->is_send = 1;
        }

        return $model->toArray();
    }

}