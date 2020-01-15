<?php

namespace GuoJiangClub\EC\Catering\Backend\Facades;

use Illuminate\Support\Facades\Facade;

class OrderService extends Facade
{
	protected static function getFacadeAccessor()
	{
		return 'OrderService';
	}
}