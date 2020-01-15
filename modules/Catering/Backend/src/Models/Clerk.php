<?php

namespace GuoJiangClub\Catering\Backend\Models;

use Illuminate\Support\Str;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class Clerk extends Authenticatable
{
	use Notifiable, HasApiTokens;

	public $table = 'st_clerk';

	public $guarded = ['id'];
}