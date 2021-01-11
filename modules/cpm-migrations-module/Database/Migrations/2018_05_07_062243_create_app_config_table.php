<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAppConfigTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('app_config');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('app_config', function (Blueprint $table) {
            $table->increments('id');
            $table->string('config_key');
            $table->string('config_value');
            $table->timestamps();
        });
    }
}
