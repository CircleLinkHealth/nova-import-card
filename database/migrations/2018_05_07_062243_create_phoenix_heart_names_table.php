<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePhoenixHeartNamesTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('phoenix_heart_names');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('phoenix_heart_names', function (Blueprint $table) {
            $table->integer('patient_id')->nullable();
            $table->string('provider_last_name')->nullable();
            $table->string('provider_first_name')->nullable();
            $table->string('patient_last_name')->nullable();
            $table->string('patient_first_name')->nullable();
            $table->string('patient_middle_name')->nullable();
            $table->string('dob')->nullable();
            $table->string('gender')->nullable();
            $table->string('email')->nullable();
            $table->string('phone_1_type')->nullable();
            $table->bigInteger('phone_1')->nullable();
            $table->string('phone_2_type')->nullable();
            $table->string('phone_2')->nullable();
            $table->string('phone_3_type')->nullable();
            $table->string('phone_3')->nullable();
            $table->string('address_1')->nullable();
            $table->string('address_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
        });
    }
}
