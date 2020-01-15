<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStActivityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('st_activity', function (Blueprint $table) {
	        $table->increments('id');
	        $table->string('title')->comment('标题');
	        $table->string('img_url')->comment('头图');
	        $table->string('img')->comment('banner');
	        $table->string('qr_code_url')->nullable()->comment('活动小程序码');
	        $table->string('address')->comment('详细地址');
	        $table->string('floor_no')->comment('所在楼层');
	        $table->timestamp('started_at')->nullable()->comment('活动开始时间');
	        $table->timestamp('ended_at')->nullable()->comment('活动结束时间');
	        $table->longText('content')->comment('活动详情');
	        $table->text('sub_activity_ids')->nullable()->comment('关联活动');
	        $table->tinyInteger('status')->default(1)->comment('状态 1:启用 0:下架');
	        $table->tinyInteger('is_recommended')->default(0)->comment('是否推荐 1:是 0:否');
	        $table->tinyInteger('is_notice')->default(0)->comment('是否开启提醒 1:是 0:否');
	        $table->unsignedInteger('collect_count')->default(0)->comment('收藏数量');
	        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('st_activity');
    }
}
