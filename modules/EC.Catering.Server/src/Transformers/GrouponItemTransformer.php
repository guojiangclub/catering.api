<?php
namespace ElementVip\Server\Transformers;

class GrouponItemTransformer extends BaseTransformer
{
    public static $excludeable = [
        'deleted_at'
    ];

    public function transformData($model)
    {
        $GrouponItem = array_except($model->toArray(), self::$excludeable);

        return $GrouponItem;
    }
}