<?php

namespace GuoJiangClub\Catering\Component\Category;

use Illuminate\Support\ServiceProvider;

class CategoryServiceProvider extends ServiceProvider
{
	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		if ($this->app->runningInConsole()) {
			$this->loadMigrationsFrom(__DIR__ . '/../migrations');

			$this->commands([
				Console\FixtreeCommand::class,
			]);
		}
	}
}
