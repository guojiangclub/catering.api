<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016-09-27
 * Time: 17:42
 */

namespace ElementVip\Server\Transformers;


class MessagesTransformer extends BaseTransformer
{

    public function transformData($model)
    {
        $fav = $model->toArray();
        return $fav;
    }

}