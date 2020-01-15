<?php

namespace GuoJiangClub\Catering\Backend;

use ElementVip\UEditor\UEditorServiceProvider;
use GuoJiangClub\Catering\Backend\Console\BackendMenusCommand;
use GuoJiangClub\Catering\Backend\Console\TestAutoSendCouponCommand;
use GuoJiangClub\Catering\Backend\Console\TestCouponOverdueRemindCommand;
use GuoJiangClub\Catering\Backend\Console\TestStatisticsResultCommand;
use GuoJiangClub\Catering\Backend\Console\UpdateUserNickName;
use GuoJiangClub\Catering\Backend\Http\Middleware\Bootstrap;
use GuoJiangClub\Catering\Backend\Schedule\AutoSendCoupon;
use GuoJiangClub\Catering\Backend\Schedule\BirthdayGiftSchedule;
use GuoJiangClub\Catering\Backend\Schedule\CouponOverdueRemind;
use GuoJiangClub\Catering\Backend\Schedule\StatisticsResultSchedule;
use Illuminate\Support\ServiceProvider;
use Route;
use Event;

class BackendProvider extends ServiceProvider
{
	protected $namespace = 'GuoJiangClub\Catering\Backend\Http\Controllers';

	protected $subscribe = [
		'GuoJiangClub\Catering\Backend\Listeners\ClerkBindEventListener',
	];

	public function boot()
	{
		if ($this->app->runningInConsole()) {
			$this->publishes([
				__DIR__ . '/../resources/assets' => public_path('assets/backend/shitang'),
			], 'backend-shitang');
		}

		foreach ($this->subscribe as $item) {
			Event::subscribe($item);
		}

		$this->loadViewsFrom(__DIR__ . '/../resources/views', 'backend-shitang');

		$this->commands([
			BackendMenusCommand::class,
			TestAutoSendCouponCommand::class,
			UpdateUserNickName::class,
			TestStatisticsResultCommand::class,
			TestCouponOverdueRemindCommand::class,
		]);

		$this->map();
	}

	public function register()
	{
		$this->app->register(UEditorServiceProvider::class);

		$this->app->make('GuoJiangClub\Catering\ScheduleList')->add(AutoSendCoupon::class);

		$this->app->make('GuoJiangClub\Catering\ScheduleList')->add(CouponOverdueRemind::class);

		$this->app->make('GuoJiangClub\Catering\ScheduleList')->add(BirthdayGiftSchedule::class);

		$this->app->make('GuoJiangClub\Catering\ScheduleList')->add(StatisticsResultSchedule::class);

		app('router')->aliasMiddleware('st.init', Bootstrap::class);
	}

	public function map()
	{
		Route::group(['middleware' => ['web', 'admin'], 'namespace' => $this->namespace, 'prefix' => 'admin'], function ($router) {
			require __DIR__ . '/Http/routes.php';
		});
	}
}