<?php

namespace GuoJiangClub\EC\Catering\Wechat\Server\Providers;

use GuoJiangClub\EC\Catering\Wechat\Server\Wx\Wx;
use Illuminate\Support\ServiceProvider;

class WechatServiceProvider extends ServiceProvider
{
    public function boot()
    {

    }

    public function register()
    {
        $this->app->singleton('wechat.channel', function () {
            return new  Wx();
        });
    }
}