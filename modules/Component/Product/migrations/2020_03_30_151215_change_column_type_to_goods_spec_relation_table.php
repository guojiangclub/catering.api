<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeColumnTypeToGoodsSpecRelationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $prefix = config('ibrand.app.database.prefix', 'ibrand_');

        if (Schema::hasTable($prefix . 'goods_spec_relation') && Schema::hasColumn($prefix.'goods_spec_relation', 'alias')) {
            Schema::table($prefix . 'goods_spec_relation', function (Blueprint $table) {
                $table->string('alias')->nullable()->change();
            });
        }

        if (Schema::hasTable($prefix . 'goods_spec_relation') && Schema::hasColumn($prefix.'goods_spec_relation', 'img')) {
            Schema::table($prefix . 'goods_spec_relation', function (Blueprint $table) {
                $table->string('img')->nullable()->change();
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
        //
    }
}
