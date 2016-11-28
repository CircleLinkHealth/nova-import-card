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
            $table->text('status', 65535);
            $table->text('license', 65535);
            $table->integer('hourly_rate');
            $table->boolean('spanish');
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
