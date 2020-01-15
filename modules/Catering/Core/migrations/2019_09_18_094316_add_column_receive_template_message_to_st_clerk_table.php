<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnReceiveTemplateMessageToStClerkTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('st_clerk', function (Blueprint $table) {
			$table->tinyInteger('receive_template_message')->default(0)->comment('接受统计模板消息')->after('is_clerk_owner');
			$table->string('openid')->nullable()->comment('公众号openid')->after('receive_template_message');
		});

		Schema::table('st_clerk_bind', function (Blueprint $table) {
			$table->dropColumn('clerk_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('st_clerk', function (Blueprint $table) {
			$table->dropColumn('receive_template_message');
			$table->dropColumn('openid');
		});
	}
}
