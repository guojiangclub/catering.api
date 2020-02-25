<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2016/10/12
 * Time: 13:09
 */


namespace ElementVip\Server\Transformers;

class BalanceTransformer extends BaseTransformer
{
    public static $excludeable = [
        'deleted_at'
    ];

    public function transformData($model)
    {
        $balance = array_except($model->toArray(), self::$excludeable);

        return $balance;
    }
}