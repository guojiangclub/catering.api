<?php

namespace GuoJiangClub\Catering\Component\Discount\Providers;

use GuoJiangClub\Catering\Component\Discount\Actions\OrderFixedDiscountAction;
use GuoJiangClub\Catering\Component\Discount\Actions\OrderPercentageDiscountAction;
use GuoJiangClub\Catering\Component\Discount\Actions\UnitFixedDiscountAction;
use GuoJiangClub\Catering\Component\Discount\Actions\UnitPercentageDiscountAction;
use GuoJiangClub\Catering\Component\Discount\Actions\UnitPercentageByMarketPriceDiscountAction;
use GuoJiangClub\Catering\Component\Discount\Actions\UnitPointTimesAction;
use GuoJiangClub\Catering\Component\Discount\Checkers\CartQuantityRuleChecker;
use GuoJiangClub\Catering\Component\Discount\Checkers\ContainsRoleRuleChecker;
use GuoJiangClub\Catering\Component\Discount\Checkers\ContainsShopsRuleChecker;
use GuoJiangClub\Catering\Component\Discount\Checkers\ItemTotalRuleChecker;
use GuoJiangClub\Catering\Component\Discount\Console\DiscountCommand;
use GuoJiangClub\Catering\Component\Discount\Models\Coupon;
use GuoJiangClub\Catering\Component\Discount\Policies\CouponPolicy;
use GuoJiangClub\Catering\Component\Discount\Repositories\CouponRepository;
use GuoJiangClub\Catering\Component\Discount\Repositories\DiscountRepository;
use GuoJiangClub\Catering\Component\Discount\Repositories\Eloquent\CouponRepositoryEloquent;
use GuoJiangClub\Catering\Component\Discount\Repositories\Eloquent\DiscountRepositoryEloquent;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use GuoJiangClub\Catering\Component\Discount\Checkers\ContainsCategoryRuleChecker;
use GuoJiangClub\Catering\Component\Discount\Checkers\ContainsProductRuleChecker;

class DiscountServiceProvider extends ServiceProvider
{
	protected $defer = false;

	protected $policies = [
		Coupon::class => CouponPolicy::class,
	];

	/**
	 * bootstrap, add routes
	 */
	public function boot(GateContract $gate)
	{
		if ($this->app->runningInConsole()) {
			$this->loadMigrationsFrom(__DIR__ . '/../../migrations');

			$this->commands([
				DiscountCommand::class,
			]);

			$this->publishes([
				__DIR__ . '/../../factories/DiscountFactory.php' => database_path('factories/DiscountFactory.php'),
			], 'discountFactory');
		}

		$this->registerPolicies($gate);
	}

	private function registerPolicies(GateContract $gate)
	{
		foreach ($this->policies as $key => $value) {
			$gate->policy($key, $value);
		}
	}

	public function register()
	{
		$this->app->bind(
			ItemTotalRuleChecker::class,
			ItemTotalRuleChecker::class
		);

		$this->app->alias(ItemTotalRuleChecker::class, ItemTotalRuleChecker::TYPE);

		$this->app->bind(
			CartQuantityRuleChecker::class,
			CartQuantityRuleChecker::class
		);
		$this->app->alias(CartQuantityRuleChecker::class, CartQuantityRuleChecker::TYPE);

		$this->app->bind(
			ContainsCategoryRuleChecker::class,
			ContainsCategoryRuleChecker::class
		);
		$this->app->alias(ContainsCategoryRuleChecker::class, ContainsCategoryRuleChecker::TYPE);

		$this->app->bind(
			ContainsRoleRuleChecker::class,
			ContainsRoleRuleChecker::class
		);
		$this->app->alias(ContainsRoleRuleChecker::class, ContainsRoleRuleChecker::TYPE);

		$this->app->bind(
			ContainsProductRuleChecker::class,
			ContainsProductRuleChecker::class
		);
		$this->app->alias(ContainsProductRuleChecker::class, ContainsProductRuleChecker::TYPE);

		$this->app->alias(ContainsShopsRuleChecker::class, ContainsShopsRuleChecker::TYPE);

		$this->app->bind(
			ContainsShopsRuleChecker::class,
			ContainsShopsRuleChecker::class
		);

		$this->app->bind(
			OrderFixedDiscountAction::class,
			OrderFixedDiscountAction::class
		);
		$this->app->alias(OrderFixedDiscountAction::class, OrderFixedDiscountAction::TYPE);

		$this->app->bind(
			OrderPercentageDiscountAction::class,
			OrderPercentageDiscountAction::class
		);
		$this->app->alias(OrderPercentageDiscountAction::class, OrderPercentageDiscountAction::TYPE);

		$this->app->bind(
			UnitFixedDiscountAction::class,
			UnitFixedDiscountAction::class
		);
		$this->app->alias(UnitFixedDiscountAction::class, UnitFixedDiscountAction::TYPE);

		$this->app->bind(
			UnitPercentageDiscountAction::class,
			UnitPercentageDiscountAction::class
		);
		$this->app->alias(UnitPercentageDiscountAction::class, UnitPercentageDiscountAction::TYPE);

		$this->app->bind(
			UnitPercentageByMarketPriceDiscountAction::class,
			UnitPercentageByMarketPriceDiscountAction::class
		);
		$this->app->alias(UnitPercentageByMarketPriceDiscountAction::class, UnitPercentageByMarketPriceDiscountAction::TYPE);

		$this->app->bind(
			UnitPointTimesAction::class,
			UnitPointTimesAction::class
		);
		$this->app->alias(UnitPointTimesAction::class, UnitPointTimesAction::TYPE);

		$this->app->bind(DiscountRepository::class, DiscountRepositoryEloquent::class);

		$this->app->singleton(CouponRepository::class, CouponRepositoryEloquent::class);
		$this->app->alias(CouponRepository::class, 'coupon.repository');
	}
}