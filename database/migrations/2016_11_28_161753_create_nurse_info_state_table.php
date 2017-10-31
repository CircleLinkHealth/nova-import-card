<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateNurseInfoStateTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nurse_info_state', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('nurse_info_id')->unsigned()->index('nurse_info_state_nurse_info_id_foreign');
            $table->integer('state_id')->unsigned()->index('nurse_info_state_states_id_foreign');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('nurse_info_state');
    }
}
