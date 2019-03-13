<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLvUsersTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('lv_users');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('lv_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique('users_email_unique');
            $table->string('password', 60);
            $table->string('remember_token', 100)->nullable();
            $table->timestamps();
        });
    }
}
