<?php

namespace GuoJiangClub\Catering\Component\Favorite\Providers;

use GuoJiangClub\Catering\Component\Product\Models\Goods;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class FavoriteServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../../migrations');
        }

        Relation::morphMap([
            'goods' => Goods::class,
        ]);
    }
}