<?php

namespace GuoJiangClub\Catering\Server\Providers;

use GuoJiangClub\Catering\Server\Actions\HotOrderFixedDiscountAction;
use GuoJiangClub\Catering\Server\Channels\BalanceChannel;
use GuoJiangClub\Catering\Server\Channels\BalancePointChannel;
use GuoJiangClub\Catering\Server\Channels\PointChannel;
use GuoJiangClub\Catering\Server\Channels\WeChatChannel;
use GuoJiangClub\Catering\Server\Http\Middleware\ClerkMiddleware;
use GuoJiangClub\Catering\Server\Repositories\CouponCenterRepository;
use GuoJiangClub\Catering\Server\Repositories\CouponRepository;
use GuoJiangClub\Catering\Server\Repositories\DiscountRepository;
use GuoJiangClub\Catering\Server\Repositories\Eloquent\CouponCenterRepositoryEloquent;
use GuoJiangClub\Catering\Server\Repositories\Eloquent\CouponRepositoryEloquent;
use GuoJiangClub\Catering\Server\Repositories\Eloquent\DiscountRepositoryEloquent;
use Illuminate\Support\ServiceProvider;
use Event;

class ServerServiceProvider extends ServiceProvider
{
	protected $subscribe = [
		'GuoJiangClub\Catering\Server\Listeners\UserPropertyChangeEvent',
	];

	public function boot()
	{
		if ($this->app->runningInConsole()) {
			$this->publishes([
				__DIR__ . '/../config.php' => config_path('ibrand/shitang-api.php'),
			]);
		}

		foreach ($this->subscribe as $item) {
			Event::subscribe($item);
		}
	}

	public function register()
	{
		$this->mergeConfigFrom(
			__DIR__ . '/../config.php', 'ibrand.shitang-api'
		);

		$this->app->register(RouteServiceProvider::class);

		$this->app->singleton(DiscountRepository::class, DiscountRepositoryEloquent::class);

		$this->app->singleton(CouponRepository::class, CouponRepositoryEloquent::class);

		$this->app->singleton(CouponCenterRepository::class, CouponCenterRepositoryEloquent::class);

		$this->app->alias(HotOrderFixedDiscountAction::class, HotOrderFixedDiscountAction::TYPE);

		$this->app->alias(BalanceChannel::class, BalanceChannel::TYPE);

		$this->app->alias(PointChannel::class, PointChannel::TYPE);

		$this->app->alias(BalancePointChannel::class, BalancePointChannel::TYPE);

		$this->app->alias(WeChatChannel::class, WeChatChannel::TYPE);

		app('router')->aliasMiddleware('st_clerk', ClerkMiddleware::class);
	}

}