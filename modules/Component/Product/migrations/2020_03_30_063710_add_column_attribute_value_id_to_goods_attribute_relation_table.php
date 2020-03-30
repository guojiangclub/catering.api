<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnAttributeValueIdToGoodsAttributeRelationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $prefix = config('ibrand.app.database.prefix', 'ibrand_');
        if (Schema::hasTable($prefix . 'goods_attribute_relation') && !Schema::hasColumn($prefix . 'goods_attribute_relation', 'attribute_value_id')) {
            Schema::table($prefix . 'goods_attribute_relation', function (Blueprint $table) {
                $table->integer('attribute_value_id')->default(0);
            });
        }

        if (Schema::hasTable($prefix . 'goods_product') && !Schema::hasColumn($prefix . 'goods_product', 'specID')) {
            Schema::table($prefix . 'goods_product', function (Blueprint $table) {
                $table->text('specID')->nullable()->after('is_show');
            });
        }

        if (Schema::hasTable($prefix . 'goods_photo') && Schema::hasColumn($prefix . 'goods_photo', 'sku')) {
            Schema::table($prefix . 'goods_photo', function (Blueprint $table) {
                $table->string('sku')->nullable()->change();
            });
        }

        if (Schema::hasTable($prefix . 'goods_spec_relation') && !Schema::hasColumn($prefix . 'goods_spec_relation', 'spec_value_id')) {
            Schema::table($prefix . 'goods_spec_relation', function (Blueprint $table) {
                $table->integer('spec_value_id')->default(0);
            });
        }

        if (Schema::hasTable($prefix . 'goods_spec_relation') && !Schema::hasColumn($prefix . 'goods_spec_relation', 'alias')) {
            Schema::table($prefix . 'goods_spec_relation', function (Blueprint $table) {
                $table->integer('alias')->nullable();
            });
        }

        if (Schema::hasTable($prefix . 'goods_spec_relation') && !Schema::hasColumn($prefix . 'goods_spec_relation', 'img')) {
            Schema::table($prefix . 'goods_spec_relation', function (Blueprint $table) {
                $table->integer('img')->nullable();
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
        if (Schema::hasTable($prefix . 'goods_attribute_relation') && Schema::hasColumn($prefix . 'goods_attribute_relation', 'attribute_value_id')) {
            Schema::table($prefix . 'goods_attribute_relation', function (Blueprint $table) {
                $table->dropColumn('attribute_value_id');
            });
        }

        if (Schema::hasTable($prefix . 'goods_product') && Schema::hasColumn($prefix . 'goods_product', 'specID')) {
            Schema::table($prefix . 'goods_product', function (Blueprint $table) {
                $table->dropColumn('specID');
            });
        }

        if (Schema::hasTable($prefix . 'goods_spec_relation') && Schema::hasColumn($prefix . 'goods_spec_relation', 'spec_value_id')) {
            Schema::table($prefix . 'goods_spec_relation', function (Blueprint $table) {
                $table->dropColumn('spec_value_id');
            });
        }

        if (Schema::hasTable($prefix . 'goods_spec_relation') && Schema::hasColumn($prefix . 'goods_spec_relation', 'alias')) {
            Schema::table($prefix . 'goods_spec_relation', function (Blueprint $table) {
                $table->dropColumn('alias');
            });
        }

        if (Schema::hasTable($prefix . 'goods_spec_relation') && Schema::hasColumn($prefix . 'goods_spec_relation', 'img')) {
            Schema::table($prefix . 'goods_spec_relation', function (Blueprint $table) {
                $table->dropColumn('img');
            });
        }
    }
}
