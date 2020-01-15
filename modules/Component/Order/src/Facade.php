<?php

namespace GuoJiangClub\Catering\Component\Order;

use Illuminate\Support\Facades\Facade as LaravelFacade;

/**
 * Facade for Laravel.
 */
class Facade extends LaravelFacade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'order';
	}
}
