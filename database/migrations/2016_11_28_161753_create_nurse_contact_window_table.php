<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateNurseContactWindowTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nurse_contact_window', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('nurse_info_id')->unsigned()->index('nurse_contact_window_nurse_info_id_foreign');
            $table->date('date');
            $table->integer('day_of_week');
            $table->time('window_time_start');
            $table->time('window_time_end');
            $table->timestamps();
            $table->softDeletes();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('nurse_contact_window');
    }

}
