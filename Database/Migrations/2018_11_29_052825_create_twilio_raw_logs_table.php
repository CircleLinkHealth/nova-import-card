<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTwilioRawLogsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('twilio_raw_logs');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('twilio_raw_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('call_sid')->nullable();
            $table->string('application_sid')->nullable();
            $table->string('account_sid')->nullable();
            $table->string('call_status')->nullable();
            $table->string('type')->nullable();
            $table->json('log')->nullable();
            $table->timestamps();
        });
    }
}
