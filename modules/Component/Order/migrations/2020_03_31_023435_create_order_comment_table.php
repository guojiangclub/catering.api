<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderCommentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $prefix = config('ibrand.app.database.prefix', 'ibrand_');

        if (!Schema::hasTable($prefix . 'order_comment')) {
            Schema::create($prefix . 'order_comment', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('order_id')->default(0);
                $table->integer('order_item_id')->default(0);
                $table->integer('item_id')->default(0)->comment('评价的商品');
                $table->text('item_meta')->nullable();
                $table->integer('user_id');
                $table->text('contents')->nullable();
                $table->integer('point')->nullable()->comment('评价分数');
                $table->string('status')->nullable()->comment('评价状态');
                $table->integer('goods_id')->default(0);
                $table->text('pic_list')->nullable();
                $table->text('reply_content')->nullable();
                $table->timestamp('reply_at')->nullable();
                $table->tinyInteger('recommend')->default(0);
                $table->timestamp('recommend_at')->nullable()->comment('推荐时间');
                $table->text('user_meta')->nullable();
                $table->timestamps();
                $table->softDeletes();
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

        Schema::dropIfExists($prefix . 'order_comment');
    }
}
