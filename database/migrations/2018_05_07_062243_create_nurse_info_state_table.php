<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateNurseInfoStateTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('nurse_info_state');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('nurse_info_state', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('nurse_info_id')->unsigned()->index('nurse_info_state_nurse_info_id_foreign');
            $table->integer('state_id')->unsigned()->index('nurse_info_state_states_id_foreign');
        });
    }
}
