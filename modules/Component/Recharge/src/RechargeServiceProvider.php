<?php

namespace GuoJiangClub\Catering\Component\Recharge;

use Illuminate\Support\ServiceProvider;
use Event;

class RechargeServiceProvider extends ServiceProvider
{

	/**
	 * bootstrap, add routes
	 */
	public function boot()
	{
		if ($this->app->runningInConsole()) {
			$this->loadMigrationsFrom(__DIR__ . '/../migrations');
		}

		Event::subscribe('GuoJiangClub\Catering\Component\Recharge\Listeners\RechargeEventListener');
	}
}
