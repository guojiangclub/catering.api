<?php

namespace GuoJiangClub\Catering\Server\Transformers;

class FavoriteTransformer extends BaseTransformer
{

    public function transformData($model)
    {
        $fav = $model->toArray();

        return $fav;
    }

}