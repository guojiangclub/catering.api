<?php

namespace GuoJiangClub\Catering\Component\Order\Models;

use GuoJiangClub\Catering\Component\Product\Models\Goods;
use GuoJiangClub\Catering\Component\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    const STATUS_SHOW   = 'show';
    const STATUS_HIDDEN = 'hidden';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $prefix = config('ibrand.app.database.prefix', 'ibrand_');

        $this->setTable($prefix . 'order_comment');
    }

    public function setItemMetaAttribute($value)
    {
        $this->attributes['item_meta'] = json_encode($value);
    }

    public function getItemMetaAttribute($value)
    {
        return json_decode($value, true);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function setPicListAttribute($value)
    {
        $this->attributes['pic_list'] = serialize($value);
    }

    public function getPicListAttribute($value)
    {
        return unserialize($value);
    }

    public function goods()
    {
        return $this->belongsTo(Goods::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    public function getUserMetaAttribute()
    {
        return json_decode($this->attributes['user_meta'], true);
    }

}
