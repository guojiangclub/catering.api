<?php
namespace GuoJiangClub\Catering\AlbumBackend\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Event;

class AlbumBackendServiceProvider extends ServiceProvider
{
    protected $namespace = 'GuoJiangClub\Catering\AlbumBackend\Http\Controllers';


    /**
     * Boot the provider.
     */
    public function boot()
    {
        $this->loadConfig();

        if (!$this->app->routesAreCached()) {
            $this->mapWebRoutes();
        }

        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'file-manage');

        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__ . '/../../resources/assets' => public_path('assets/backend/file-manage')
            ], 'file-manage-assets');

	        $this->loadMigrationsFrom(__DIR__ . '/../../migrations');
        }

        Event::subscribe('GuoJiangClub\Catering\AlbumBackend\Listeners\UploadListeners');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config.php', 'dmp-file-manage'
        );
    }

    /**
     * Register config.
     */
    protected function loadConfig()
    {
        $this->publishes([
            __DIR__.'/../config.php' => config_path('dmp-file-manage.php'),
        ]);
    }

    /**
     * Register routes.
     */
    protected function mapWebRoutes()
    {
        Route::group([
            'middleware' => ['web'],
            'namespace' => $this->namespace,
        ], function ($router) {
            require __DIR__ . '/../Http/routes.php';
        });
    }
}