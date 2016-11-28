<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePracticeUserTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('practice_user', function (Blueprint $table) {
            $table->integer('user_id')->unsigned()->index('practice_user_user_id_foreign');
            $table->integer('program_id')->unsigned()->index('lv_program_user_program_id_foreign');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('practice_user');
    }

}
