<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCareAmbassadorsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('care_ambassadors', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index('care_ambassadors_user_id_foreign');
            $table->integer('hourly_rate')->unsigned()->nullable();
            $table->boolean('speaks_spanish');
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
        Schema::drop('care_ambassadors');
    }
}
