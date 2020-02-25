<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016-08-23
 * Time: 13:25
 */

namespace ElementVip\Server\Transformers;

class PointTransformer extends BaseTransformer
{
    public static $excludeable = [
        'deleted_at'
    ];

    public function transformData($model)
    {
        $status_text = '';
        if ($model->status == 0) {
            $status_text = '积分冻结';
        } else {
            if ($model->expired == 0) {
                $status_text = '积分生效';
            } else {
                $status_text = '积分过期';
            }
        }

        $model->status_text = $status_text;
        $point = array_except($model->toArray(), self::$excludeable);

        return $point;
    }
}