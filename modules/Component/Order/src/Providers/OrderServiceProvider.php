<?php

namespace GuoJiangClub\Catering\Component\Order\Providers;

use GuoJiangClub\Catering\Component\Order\Models\Order;
use GuoJiangClub\Catering\Component\Order\Observers\OrderObserver;
use GuoJiangClub\Catering\Component\Order\Policies\OrderPolicy;
use GuoJiangClub\Catering\Component\Order\Repositories\Eloquent\OrderRepositoryEloquent;
use GuoJiangClub\Catering\Component\Order\Repositories\OrderRepository;
use GuoJiangClub\Catering\Component\Order\Schedule;
use Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;

/**
 * Service provider for Laravel.
 */
class OrderServiceProvider extends ServiceProvider
{
	protected $policies = [
		Order::class => OrderPolicy::class,
	];

	/**
	 * 要注册的订阅者类。
	 *
	 * @var array
	 */
	protected $subscribe = [
		'GuoJiangClub\Catering\Component\Order\Listeners\OrderEventListener',
	];

	/**
	 * Boot the provider.
	 */
	public function boot(GateContract $gate)
	{
		if ($this->app->runningInConsole()) {
			$this->loadMigrationsFrom(__DIR__ . '/../../migrations');
		}
		//require __DIR__ . '/../helpers.php';

		$this->registerPolicies($gate);

		foreach ($this->subscribe as $subscriber) {
			Event::subscribe($subscriber);
		}

		Order::observe(OrderObserver::class);
	}

	private function registerPolicies(GateContract $gate)
	{
		foreach ($this->policies as $key => $value) {
			$gate->policy($key, $value);
		}
	}

	/**
	 * Register the service provider.
	 */
	public function register()
	{
		$this->app->bind(OrderRepository::class, OrderRepositoryEloquent::class);

		$this->app->make('GuoJiangClub\Catering\ScheduleList')->add(Schedule::class);
	}
}
