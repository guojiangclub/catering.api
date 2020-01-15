<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToStGiftActivityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('st_gift_activity', function (Blueprint $table) {
            if (!Schema::hasColumn('st_gift_activity', 'activity_type')) {
                $table->string('activity_type')->default('gift_new_user'); //类型：gift_new_user 新人礼；gift_birthday 生日礼
            }

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('st_gift_activity', function (Blueprint $table) {
            $table->dropColumn('activity_type');
        });
    }
}
