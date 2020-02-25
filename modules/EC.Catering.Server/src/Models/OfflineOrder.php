<?php

namespace ElementVip\Server\Models;

use Illuminate\Database\Eloquent\Model;

class OfflineOrder extends Model
{
    protected $table = 'el_offline_order';
    protected $guarded = ['id'];

    public function goods()
    {
        return $this->hasMany(OfflineOrderGoods::class, 'order_id');
    }
}
