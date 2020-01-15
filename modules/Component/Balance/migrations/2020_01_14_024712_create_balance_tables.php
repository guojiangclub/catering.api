<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBalanceTables extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$prefix = config('ibrand.app.database.prefix', 'ibrand_');
		if (!Schema::hasTable($prefix . 'balance')) {
			Schema::create($prefix . 'balance', function (Blueprint $table) {
				$table->increments('id');
				$table->integer('user_id')->unsigned();
				$table->integer('type')->nullable();
				$table->string('note')->nullable();
				$table->integer('value');
				$table->integer('current_balance')->default(0);
				$table->integer('origin_id')->default(0);
				$table->string('origin_type')->nullable();

				$table->timestamps();
				$table->softDeletes();
			});
		}

		if (!Schema::hasTable($prefix . 'balance_cash')) {
			Schema::create($prefix . 'balance_cash', function (Blueprint $table) {
				$table->increments('id');
				$table->integer('user_id')->comment('用户id');
				$table->integer('agent_id')->default(0);
				$table->string('cash_no')->comment('提现单号');
				$table->integer('amount')->default(0)->comment('金额');
				$table->integer('balance')->default(0)->comment('余额');
				$table->tinyInteger('status')->default(0)->comment('状态 0：待审核  1:待打款提现 2：已打款提现  3:审核不通过');
				$table->dateTime('settle_time')->nullable()->comment('打款时间');
				$table->string('cert')->nullable()->comment('打款凭证');
				$table->integer('bank_account_id')->comment('账号ID');
				$table->string('bank_name')->comment('银行名称');
				$table->string('bank_number')->comment('账号');
				$table->string('owner_name')->comment('收款姓名');
				$table->string('cash_type')->default('微信钱包：customer_wechat, 银行卡：customer_account');
				$table->timestamps();
				$table->softDeletes();
			});
		}

		if (!Schema::hasTable($prefix . 'balance_order')) {
			Schema::create($prefix . 'balance_order', function (Blueprint $table) {
				$table->increments('id');
				$table->integer('user_id')->unsigned();
				$table->string('order_no')->comment('订单编号');
				$table->string('pay_type')->nullable()->comment('支付方式 包含支付宝，微信，余额支付');
				$table->integer('pay_status')->unsigned()->default(0);
				$table->timestamp('pay_time')->nullable();
				$table->integer('amount')->comment('到账金额');
				$table->integer('pay_amount')->comment('实际支付金额');
				$table->string('note')->nullable()->comment('管理员备注');
				$table->integer('recharge_rule_id')->nullable();
				$table->nullableTimestamps();
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

		Schema::dropIfExists($prefix . 'balance');
		Schema::dropIfExists($prefix . 'balance_cash');
	}
}
