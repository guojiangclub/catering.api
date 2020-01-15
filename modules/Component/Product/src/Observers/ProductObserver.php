<?php
namespace GuoJiangClub\Catering\Component\Product\Observers;

use GuoJiangClub\Catering\Component\Product\Models\Product;

class ProductObserver
{
    public function saved(Product $product)
    {
        if ($goods = $product->goods) { //如果启用了价格保护，则自动下架
            $goods->calculateStock();
            $goods->save();
        }
    }
}