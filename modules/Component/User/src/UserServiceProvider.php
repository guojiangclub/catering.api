<?php

namespace GuoJiangClub\Catering\Component\User;

use GuoJiangClub\Catering\Component\User\Models\User;
use GuoJiangClub\Catering\Component\User\Observers\UserObserver;
use GuoJiangClub\Catering\Component\User\Repository\Eloquent\UserBindRepositoryEloquent;
use GuoJiangClub\Catering\Component\User\Repository\Eloquent\UserRepositoryEloquent;
use GuoJiangClub\Catering\Component\User\Repository\UserBindRepository;
use GuoJiangClub\Catering\Component\User\Repository\UserRepository;
use Illuminate\Support\ServiceProvider;

class UserServiceProvider extends ServiceProvider
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
		}

		User::observe(UserObserver::class);
	}

	public function register()
	{
		$this->app->register(ShaHashServiceProvider::class);

		$this->app->bind(UserBindRepository::class, UserBindRepositoryEloquent::class);

		$this->app->bind(UserRepository::class, UserRepositoryEloquent::class);
	}
}
