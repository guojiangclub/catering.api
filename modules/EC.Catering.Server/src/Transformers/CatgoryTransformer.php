<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2016/9/30
 * Time: 15:27
 */

namespace ElementVip\Server\Transformers;

class CatgoryTransformer extends BaseTransformer
{



    public function transformData($model)
    {

        return $model->toArray();
    }



}