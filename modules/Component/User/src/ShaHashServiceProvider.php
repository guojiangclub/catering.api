<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016-11-30
 * Time: 20:01
 */

namespace GuoJiangClub\Catering\Component\User;


use Illuminate\Hashing\HashServiceProvider;

class ShaHashServiceProvider extends HashServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    public function register()
    {
        $this->app->singleton('hash', function () {
            return new ShaHasher();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['hash'];
    }
}