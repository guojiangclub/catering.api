<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateELFavoritesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $prefix = config('ibrand.app.database.prefix', 'ibrand_');

        if (!Schema::hasTable($prefix . 'favorites')) {
            Schema::create($prefix . 'favorites', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->integer('favoriteable_id')->comment('收藏的id');
                $table->string('favoriteable_type')->comment('收藏的类型(如：商品 ，故事)');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $prefix = config('ibrand.app.database.prefix', 'ibrand_');

        Schema::drop($prefix . 'favorites');
    }

}
