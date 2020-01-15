<?php

namespace GuoJiangClub\Catering\Core\Models;


use ElementVip\Component\User\Models\User;
use GuoJiangClub\Catering\Backend\Models\Coupon\Coupon;
use Illuminate\Database\Eloquent\Model;

class GiftCouponReceive extends Model
{
    public $table = 'st_gift_coupon_receive';

    public $guarded = ['id'];

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'discount_id', 'discount_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}