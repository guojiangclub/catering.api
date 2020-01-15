<?php

namespace GuoJiangClub\Catering\Server\Providers;

use GuoJiangClub\Catering\Server\Serializer\DataArraySerializer;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use League\Fractal\Manager;
use Dingo\Api\Transformer\Adapter\Fractal;

class RouteServiceProvider extends ServiceProvider
{
	protected $namespace = 'GuoJiangClub\Catering\Server\Http\Controllers';

	public function boot()
	{
		parent::boot();

		$this->app['Dingo\Api\Transformer\Factory']->setAdapter(function ($app) {

			$fractal = new Manager();

			$fractal->setSerializer(new DataArraySerializer());

			return new Fractal($fractal);
		});
	}

	public function map()
	{
		$this->mapApiRoutes();
	}

	protected function mapApiRoutes()
	{
		$api = app('Dingo\Api\Routing\Router');

		$api->version('v1',
			array_merge(config('ibrand.shitang-api.routeAttributes'), ['namespace' => $this->namespace]), function ($router) {
				require __DIR__ . '/../Http/routes.php';
			});
	}
}