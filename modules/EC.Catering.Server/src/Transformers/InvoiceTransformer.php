<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2016/10/12
 * Time: 13:09
 */


namespace ElementVip\Server\Transformers;

class InvoiceTransformer extends BaseTransformer
{

    public function transformData($model)
    {

        $invoice = $model->toArray();

        return $invoice;
    }


}