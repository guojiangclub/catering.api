<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/6/21
 * Time: 18:54
 */

namespace ElementVip\Server\Transformers;


class BalanceCashTransformer extends BaseTransformer
{
    public static $excludeable = [
        'deleted_at'
    ];

    public function transformData($model)
    {
        $balanceCash = array_except($model->toArray(), self::$excludeable);

        return $balanceCash;
    }
}