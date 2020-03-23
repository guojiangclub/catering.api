<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixTableGiftCouponReceiveError extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $prefix = config('ibrand.app.database.prefix', 'ibrand_');

        if (Schema::hasTable($prefix . 'gift_coupon_receive') && Schema::hasColumn($prefix . 'gift_coupon_receive', 'type_id')) {
            Schema::table($prefix . 'gift_coupon_receive', function(Blueprint $table){
                $table->dropColumn('type_id');
            });
        }

        if (Schema::hasTable($prefix . 'gift_coupon_receive') && Schema::hasColumn($prefix . 'gift_coupon_receive', 'balance_order_id')) {
            Schema::table($prefix . 'gift_coupon_receive', function(Blueprint $table){
                $table->dropColumn('balance_order_id');
            });
        }

        if (Schema::hasTable($prefix . 'gift_coupon_receive') && !Schema::hasColumn($prefix . 'gift_coupon_receive', 'type')) {
            Schema::table($prefix . 'gift_coupon_receive', function(Blueprint $table){
                $table->string('type')->nullable()->after('id');
            });
        }

        if (Schema::hasTable($prefix . 'gift_coupon_receive') && !Schema::hasColumn($prefix . 'gift_coupon_receive', 'origin_type')) {
            Schema::table($prefix . 'gift_coupon_receive', function(Blueprint $table){
                $table->string('origin_type')->nullable()->after('type');
            });
        }

        if (Schema::hasTable($prefix . 'gift_coupon_receive') && !Schema::hasColumn($prefix . 'gift_coupon_receive', 'origin_id')) {
            Schema::table($prefix . 'gift_coupon_receive', function(Blueprint $table){
                $table->integer('origin_id')->nullable()->after('origin_type');
            });
        }

        if (Schema::hasTable($prefix . 'gift_coupon_receive') && !Schema::hasColumn($prefix . 'gift_coupon_receive', 'coupon_id')) {
            Schema::table($prefix . 'gift_coupon_receive', function(Blueprint $table){
                $table->integer('coupon_id')->nullable()->after('discount_id');
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
