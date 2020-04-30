<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $prefix = config('ibrand.app.database.prefix', 'ibrand_');
        if (!Schema::hasTable($prefix . 'address')) {
            Schema::create($prefix . 'addresses', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->string('accept_name');
                $table->string('mobile');
                $table->integer('province');
                $table->integer('city');
                $table->integer('area');
                $table->string('address_name');
                $table->string('address');
                $table->tinyInteger('is_default')->default(0);
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

        Schema::dropIfExists($prefix . 'address');
    }
}
