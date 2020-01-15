<?php

namespace GuoJiangClub\Catering\Component\Scheduling\Providers;

use GuoJiangClub\Catering\Component\Scheduling\Schedule\ScheduleHandle;
use GuoJiangClub\Catering\Component\Scheduling\Schedule\ScheduleList;
use Illuminate\Support\ServiceProvider;

class SchedulingProvider extends ServiceProvider
{

	/**
	 * Define your route model bindings, pattern filters, etc.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->app->make('GuoJiangClub\Catering\Component\Scheduling\Schedule\ScheduleHandle');
	}

	public function register()
	{
		$this->app->singleton('GuoJiangClub\Catering\Component\Scheduling\Schedule\ScheduleHandle', function ($app) {
			return new ScheduleHandle($app);
		});

		$this->app->singleton('GuoJiangClub\Catering\ScheduleList', function () {
			return new ScheduleList();
		});
	}

}
