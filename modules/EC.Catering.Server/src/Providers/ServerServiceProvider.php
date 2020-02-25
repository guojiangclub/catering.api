<?php

namespace GuoJiangClub\EC\Catering\Server\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class ServerServiceProvider extends ServiceProvider
{
    protected function boot()
    {
    }

    public function register()
    {
        $app = $this->app;

        $app->register(RouteServiceProvider::class);

        app('api.exception')->register(function (\Exception $exception) {
            $request = Request::capture();

            return app('GuoJiangClub\EC\Catering\Server\Exception\ApiException')->render($request, $exception);
        });

        $this->app['config']->set('filesystems.disks.public.url', env('APP_URL') . '/storage/');
    }

}