<?php

namespace GuoJiangClub\Catering\Backend\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ClerkBind extends Model
{
	public $table = 'st_clerk_bind';

	public $guarded = ['id'];
}