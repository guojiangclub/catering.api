<?php

namespace GuoJiangClub\Catering\Backend\Models;

use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
	public $table = 'st_order_refund';

	public $guarded = ['id'];
}