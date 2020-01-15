<?php

namespace GuoJiangClub\Catering\Component\Point;

use GuoJiangClub\Catering\Component\Point\Console\PointGoodsCommand;
use Illuminate\Support\ServiceProvider;
use Event;

class PointServiceProvider extends ServiceProvider
{

	/**
	 * bootstrap, add routes
	 */
	public function boot()
	{
		if ($this->app->runningInConsole()) {
			$this->loadMigrationsFrom(__DIR__ . '/../migrations');
		}

		$this->commands([
			PointGoodsCommand::class,
		]);

		Event::subscribe('GuoJiangClub\Catering\Component\Point\Listeners\PointEventListener');
	}

	/**
	 * register the service provider
	 */
	public function register()
	{
		$this->app->make('GuoJiangClub\Catering\ScheduleList')->add(Schedule::class);
	}

	public function provides()
	{
		return ['Point'];
	}
}
