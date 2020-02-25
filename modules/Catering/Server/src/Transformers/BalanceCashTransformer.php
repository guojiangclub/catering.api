<?php

namespace GuoJiangClub\Catering\Server\Transformers;

class BalanceCashTransformer extends BaseTransformer
{
    public static $excludeable = [
        'deleted_at',
    ];

    public function transformData($model)
    {
        $balanceCash = array_except($model->toArray(), self::$excludeable);

        return $balanceCash;
    }
}