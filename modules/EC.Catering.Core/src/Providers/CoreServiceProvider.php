<?php

/*
 * This file is part of ibrand/EC-Open-Core.
 *
 * (c) 果酱社区 <https://guojiang.club>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GuoJiangClub\EC\Catering\Core\Providers;

use GuoJiangClub\Catering\Component\Balance\BalanceServiceProvider;
use GuoJiangClub\Catering\Component\Brand\BrandServiceProvider;
use GuoJiangClub\Catering\Component\Category\CategoryServiceProvider;
use GuoJiangClub\Catering\Component\Discount\Providers\DiscountServiceProvider;
use GuoJiangClub\Catering\Component\Favorite\Providers\FavoriteServiceProvider;
use GuoJiangClub\Catering\Component\Gift\GiftServiceProvider;
use GuoJiangClub\Catering\Component\Order\Providers\OrderServiceProvider;
use GuoJiangClub\Catering\Component\Payment\Providers\PaymentServiceProvider;
use GuoJiangClub\Catering\Component\Point\PointServiceProvider;
use GuoJiangClub\Catering\Component\Product\ProductServiceProvider;
use GuoJiangClub\Catering\Component\Recharge\RechargeServiceProvider;
use GuoJiangClub\Catering\Component\Scheduling\Providers\SchedulingProvider;
use GuoJiangClub\Catering\Component\Shipping\ShippingServiceProvider;
use GuoJiangClub\Catering\Component\User\UserServiceProvider;
use GuoJiangClub\Catering\AlbumBackend\Providers\AlbumBackendServiceProvider;
use GuoJiangClub\Catering\Component\Address\Providers\AddressServiceProvider;
use Illuminate\Support\ServiceProvider;
use Schema;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        if (config('ibrand.app.secure')) {
            \URL::forceScheme('https');
        }

        Schema::defaultStringLength(191);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/app.php' => config_path('ibrand/app.php'),
            ]);
        }

        $this->loadMigrationsFrom(__DIR__ . '/../../migrations');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/app.php', 'ibrand.app'
        );

        $this->registerComponent();
    }

    public function registerComponent()
    {
        $this->app->register(UserServiceProvider::class);
        $this->app->register(SchedulingProvider::class);
        $this->app->register(PointServiceProvider::class);
        $this->app->register(RechargeServiceProvider::class);
        $this->app->register(BalanceServiceProvider::class);
        $this->app->register(ProductServiceProvider::class);
        $this->app->register(OrderServiceProvider::class);
        $this->app->register(DiscountServiceProvider::class);
        $this->app->register(PaymentServiceProvider::class);
        $this->app->register(BrandServiceProvider::class);
        $this->app->register(CategoryServiceProvider::class);
        $this->app->register(ShippingServiceProvider::class);
        $this->app->register(AlbumBackendServiceProvider::class);
        $this->app->register(GiftServiceProvider::class);
        $this->app->register(FavoriteServiceProvider::class);
        $this->app->register(AddressServiceProvider::class);
    }
}
