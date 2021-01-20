<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePhoneNumbersTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('phone_numbers');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('phone_numbers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index();
            $table->integer('location_id')->unsigned()->nullable();
            $table->string('number')->nullable();
            $table->string('extension')->nullable();
            $table->string('type')->nullable();
            $table->boolean('is_primary')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
