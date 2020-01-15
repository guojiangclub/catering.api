<?php

namespace GuoJiangClub\Catering\Component\Payment\Providers;

use GuoJiangClub\Catering\Component\Payment\Charges\DefaultCharge;
use GuoJiangClub\Catering\Component\Payment\Charges\PingxxCharge;
use GuoJiangClub\Catering\Component\Payment\Charges\UnionCharge;
use GuoJiangClub\Catering\Component\Payment\Contracts\PaymentChargeContract;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
	public function boot()
	{
		if ($this->app->runningInConsole()) {
			$this->loadMigrationsFrom(__DIR__ . '/../../migrations');
		}

		$this->publishes([$this->configPath() => config_path('payment.php')]);

		$this->publishes([$this->configPay() => config_path('pay.php')], 'pay');
	}

	public function register()
	{
		$this->mergeConfigFrom($this->configPath(), 'payment');

		$this->app->singleton(PaymentChargeContract::class, function ($app) {

			if (settings('enabled_pingxx_pay')) {
				return new PingxxCharge('pingxx');
			}

			if (settings('enabled_union_pay')) {
				return new UnionCharge('union');
			}

			return new DefaultCharge('default');
		});
	}

	protected function configPath()
	{
		return __DIR__ . '/../../config/payment.php';
	}

	protected function configPay()
	{
		return __DIR__ . '/../../config/pay.php';
	}
}