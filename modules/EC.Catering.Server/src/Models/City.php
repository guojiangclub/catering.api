<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016-07-19
 * Time: 13:52
 */

namespace ElementVip\Server\Models;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class City extends Model implements Transformable
{
    use TransformableTrait;

    protected $table = 'storelocator_citys';
    protected $guarded = ['id'];

    public function scopeAllCity($query)
    {
        return $query->orderBy('letter', 'asc')->get();
    }
}
