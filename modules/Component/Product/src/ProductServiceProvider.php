<?php

namespace GuoJiangClub\Catering\Component\Product;

use GuoJiangClub\Catering\Component\Product\Models\Product;
use GuoJiangClub\Catering\Component\Product\Observers\ProductObserver;
use GuoJiangClub\Catering\Component\Product\Repositories\Eloquent\GoodsRepositoryEloquent;
use GuoJiangClub\Catering\Component\Product\Repositories\Eloquent\ProductRepositoryEloquent;
use GuoJiangClub\Catering\Component\Product\Repositories\GoodsRepository;
use GuoJiangClub\Catering\Component\Product\Repositories\ProductRepository;
use Illuminate\Support\ServiceProvider;
use Event;

class ProductServiceProvider extends ServiceProvider
{

	protected $subscribe = [
		'GuoJiangClub\Catering\Component\Product\Listeners\ProductEventListener',
	];

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

		foreach ($this->subscribe as $subscriber) {
			Event::subscribe($subscriber);
		}

		Product::observe(ProductObserver::class);
	}

	public function register()
	{
		$this->app->bind(GoodsRepository::class, GoodsRepositoryEloquent::class);
		$this->app->bind(ProductRepository::class, ProductRepositoryEloquent::class);
	}
}
