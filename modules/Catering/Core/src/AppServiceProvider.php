<?php

namespace GuoJiangClub\Catering\Core;

use GuoJiangClub\Catering\Component\Order\Repositories\OrderRepository;
use GuoJiangClub\Catering\Component\User\Repository\UserRepository;
use GuoJiangClub\Catering\Component\User\UserServiceProvider;
use GuoJiangClub\Catering\Backend\Models\Coupon\Coupon;
use GuoJiangClub\Catering\Core\Auth\User;
use GuoJiangClub\Catering\Core\Policies\CouponPolicy;
use GuoJiangClub\Catering\Core\Repository\Eloquent\OrderRepositoryEloquent;
use GuoJiangClub\Catering\Core\Repository\Eloquent\UserRepositoryEloquent;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Schema;
use GuoJiangClub\Catering\Component\User\Models\User as BaseUser;
use Event;

class AppServiceProvider extends ServiceProvider
{

	protected $policies = [
		Coupon::class => CouponPolicy::class,
	];

	protected $subscribe = [
		'GuoJiangClub\Catering\Core\Listeners\UserRegisterListener',
		'GuoJiangClub\Catering\Core\Listeners\UpdateProfileListener',
		'GuoJiangClub\Catering\Core\Listeners\DirectCouponListener',
		'GuoJiangClub\Catering\Core\Listeners\WechatTemplateMessageListener',
		'GuoJiangClub\Catering\Core\Listeners\StatisticsResultListener',
	];

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot(GateContract $gate)
	{
		if (config('ibrand.app.secure')) {
			\URL::forceScheme('https');
		}

		Schema::defaultStringLength(191);

		/*if ($this->app->runningInConsole()) {

			$this->loadMigrationsFrom(__DIR__ . '/../migrations');
		}*/

		$this->registerPolicies($gate);

		foreach ($this->subscribe as $item) {
			Event::subscribe($item);
		}
	}

	protected function registerPolicies(GateContract $gate)
	{
		foreach ($this->policies as $key => $value) {
			$gate->policy($key, $value);
		}
	}

	public function register()
	{
		$this->app->bind(BaseUser::class, User::class);

		$this->app->bind(UserRepository::class, UserRepositoryEloquent::class);

		$this->app->bind(OrderRepository::class, OrderRepositoryEloquent::class);
	}

}
