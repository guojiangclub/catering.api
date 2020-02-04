<?php

namespace GuoJiangClub\Catering\Component\Gift;

use Illuminate\Support\ServiceProvider;
use Event;

class GiftServiceProvider extends ServiceProvider
{

	/**
	 * bootstrap, add routes
	 */
	public function boot()
	{
		Event::subscribe('GuoJiangClub\Catering\Component\Gift\Listeners\NewUser\GiftEventListener');
		Event::subscribe('GuoJiangClub\Catering\Component\Gift\Listeners\Birthday\GiftEventListener');
		Event::subscribe('GuoJiangClub\Catering\Component\Gift\Listeners\DirectionalCoupon\GiftEventListener');
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

	}
}
