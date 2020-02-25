<?php
/**
 * Created by PhpStorm.
 * User: Chenhao
 * Date: 2016-07-19
 * Time: 13:02
 */

namespace ElementVip\Server\Models;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class Tag extends Model implements Transformable
{
    use TransformableTrait;

    protected $table = 'storelocator_tags';

    protected $guarded = ['id'];


}