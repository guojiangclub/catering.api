<?php

namespace GuoJiangClub\Catering\Component\Balance;

use Illuminate\Support\ServiceProvider;
use Event;

class BalanceServiceProvider extends ServiceProvider
{

	/**
	 * bootstrap, add routes
	 */
	public function boot()
	{
		if ($this->app->runningInConsole()) {
			$this->loadMigrationsFrom(__DIR__ . '/../migrations');
		}

		Event::subscribe('GuoJiangClub\Catering\Component\Balance\Listeners\balanceRefundEventListener');
	}
}
