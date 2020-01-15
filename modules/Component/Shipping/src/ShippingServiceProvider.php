<?php

namespace GuoJiangClub\Catering\Component\Shipping;

use GuoJiangClub\Catering\Component\Shipping\Console\ShippingCommand;
use Illuminate\Support\ServiceProvider;

class ShippingServiceProvider extends ServiceProvider
{
	public function boot()
	{
		if ($this->app->runningInConsole()) {
			$this->loadMigrationsFrom(__DIR__ . '/../migrations');
		}

		$this->commands([
			ShippingCommand::class,
		]);
	}
}