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

class Shop extends Model implements Transformable
{
    use TransformableTrait;

    protected $table = 'storelocator';
    protected $guarded = ['id'];

    public function scopeCity($query, $cityId)
    {
        if ($cityId) {
            return $query->with('tag')->where('city', $cityId);
        }
        return $query->with('tag')->all();
    }

    //protected $appends = ['pic'];

    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }

    /*public function getPicAttribute(){
        if($tag = $this->tag){
            return  $tag->file_url;
        }
        return '';
    }*/
}