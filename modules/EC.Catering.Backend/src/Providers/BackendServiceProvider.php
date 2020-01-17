<?php

/*
 * This file is part of ibrand/catering-backend.
 *
 * (c) iBrand <https://www.ibrand.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GuoJiangClub\EC\Catering\Backend\Providers;

use GuoJiangClub\EC\Catering\Backend\Console\SetDefaultValueCommand;
use GuoJiangClub\EC\Catering\Backend\Console\SpecCommand;
use GuoJiangClub\EC\Catering\Backend\Service\ExcelExportsService;
use GuoJiangClub\EC\Catering\Backend\Service\GoodsService;
use GuoJiangClub\EC\Catering\Backend\Service\OrderService;
use GuoJiangClub\EC\Catering\Backend\Console\BackendMenusCommand;
use GuoJiangClub\EC\Catering\Backend\Console\InstallCommand;
use GuoJiangClub\EC\Catering\Backend\Console\RolesCommand;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class BackendServiceProvider extends ServiceProvider
{
	/**
	 * This namespace is applied to your controller routes.
	 *
	 * In addition, it is set as the URL generator's root namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'GuoJiangClub\EC\Catering\Backend\Http\Controllers';

	/**
	 * Define your route model bindings, pattern filters, etc.
	 */
	public function boot()
	{
		if (!$this->app->routesAreCached()) {
			$this->mapWebRoutes();
		}

		$this->loadViewsFrom(__DIR__ . '/../../resources/views', 'catering-backend');

		if ($this->app->runningInConsole()) {
			$this->loadMigrationsFrom(__DIR__ . '/../../migrations');

			$this->publishes([
				__DIR__ . '/../../resources/assets/template' => public_path('assets/template'),
			], 'catering-backend-assets');

			$this->publishes([
				__DIR__ . '/../../resources/assets/libs' => public_path('assets/backend/libs'),
			], 'catering-backend-assets-libs');

			$this->publishes([
				__DIR__ . '/../../resources/assets/css' => public_path('assets/backend/css'),
			], 'catering-backend-assets-css');

			$this->publishes([
				__DIR__ . '/../../resources/assets/images' => public_path('assets/backend/images'),
			], 'catering-backend-assets-images');
		}

		$this->commands([
			InstallCommand::class,
			BackendMenusCommand::class,
			RolesCommand::class,
			SetDefaultValueCommand::class,
			SpecCommand::class,
		]);
	}

	public function register()
	{
		$this->app->singleton('GoodsService', function () {
			return new  GoodsService();
		});

		$this->app->singleton('OrderService', function () {
			return new  OrderService();
		});

		$this->app->singleton('ExcelExportsService', function () {
			return new  ExcelExportsService();
		});
	}

	/**
	 * Define the "web" routes for the application.
	 *
	 * These routes all receive session state, CSRF protection, etc.
	 */
	protected function mapWebRoutes()
	{
		Route::group([
			'middleware' => ['web', 'admin'],
			'namespace'  => $this->namespace,
		], function ($router) {
			require __DIR__ . '/../Http/routes.php';
		});
	}
}
