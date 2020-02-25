<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016-08-23
 * Time: 13:25
 */

namespace ElementVip\Server\Transformers;

class CardTransformer extends BaseTransformer
{
    public static $excludeable = [
        'deleted_at'
    ];

    public function transformData($model)
    {
        $card = array_except($model->toArray(), self::$excludeable);

        return $card;
    }
}