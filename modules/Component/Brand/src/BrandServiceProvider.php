<?php

namespace GuoJiangClub\Catering\Component\Brand;

use Illuminate\Support\ServiceProvider;

class BrandServiceProvider extends ServiceProvider
{
	public function boot()
	{
		if ($this->app->runningInConsole()) {
			$this->loadMigrationsFrom(__DIR__ . '/../migrations');
		}
	}
}