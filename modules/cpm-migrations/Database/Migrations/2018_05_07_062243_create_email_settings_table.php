<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEmailSettingsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('email_settings');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('email_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index('email_settings_user_id_foreign');
            $table->string('frequency');
            $table->timestamps();
        });
    }
}
