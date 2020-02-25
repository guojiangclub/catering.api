<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016-09-27
 * Time: 17:42
 */

namespace ElementVip\Server\Transformers;

use ElementVip\Component\User\Models\User;

class CommentTransformer extends BaseTransformer
{
    public function transformData($model)
    {
        if ($model->user_meta) {
            $model->user = $model->user_meta;
        } else {
            $model->user = User::find($model->user_id);
        }
        return $model->toArray();
    }
}