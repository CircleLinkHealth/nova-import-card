<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTwilioRecordingsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('twilio_recordings');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('twilio_recordings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('account_sid');
            $table->string('call_sid');
            $table->string('conference_sid')->nullable();
            $table->string('source');
            $table->string('status');
            $table->string('url')->nullable();
            $table->integer('duration')->default(0);
            $table->timestamps();
        });
    }
}
