<?php

namespace GuoJiangClub\EC\Catering\Server\Providers;

use ElementVip\Server\Serializer\DataArraySerializer;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Route;
use League\Fractal\Manager;
use Dingo\Api\Transformer\Adapter\Fractal;

class RouteServiceProvider extends ServiceProvider
{

    /**
     * This namespace is applied to the controller routes in your routes file.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'ElementVip\Server\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @param \Illuminate\Routing\Router $router
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        $this->app['Dingo\Api\Transformer\Factory']->setAdapter(function ($app) {

            $fractal = new Manager();

            $fractal->setSerializer(new DataArraySerializer());

            return new Fractal($fractal);
        });

        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'server');

        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__ . '/../../resources/assets' => public_path('assets/server'),
            ], 'server-assets');

            $this->publishes([
                __DIR__ . '/../config.php' => config_path('dmp-api.php'),
            ]);

            $this->publishes([__DIR__ . '/../../config/config.php' => config_path('phantommagick.php')], 'config');

            $this->loadMigrationsFrom(__DIR__ . '/../../migrations');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config.php', 'dmp-api'
        );

        $config = $this->app['config']->get('filesystems.disks', []);

        $this->app['config']->set('filesystems.disks', array_merge(config('phantommagick.disks', []), $config));
    }

    /**
     * Define the routes for the application.
     *
     * @param \Illuminate\Routing\Router $router
     *
     * @return void
     */
    public function map()
    {
        $api = app('Dingo\Api\Routing\Router');

        $api->version('v1',
            array_merge(config('dmp-api.routeAttributes'), ['namespace' => $this->namespace]), function ($router) {
                require __DIR__ . '/../Http/routes.php';
            });
    }
}