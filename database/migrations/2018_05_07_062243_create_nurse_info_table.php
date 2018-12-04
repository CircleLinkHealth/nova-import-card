<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateNurseInfoTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nurse_info', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index('nurse_info_user_id_foreign');
            $table->text('status', 65535)->nullable();
            $table->text('license', 65535)->nullable();
            $table->integer('hourly_rate')->default(0);
            $table->string('billing_type')->default('fixed');
            $table->integer('low_rate')->default(10);
            $table->integer('high_rate')->default(30);
            $table->boolean('spanish')->default(0);
            $table->timestamps();
            $table->boolean('isNLC')->default(0);
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('nurse_info');
    }
}
