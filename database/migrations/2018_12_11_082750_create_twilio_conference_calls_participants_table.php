<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTwilioConferenceCallsParticipantsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('twilio_conference_calls_participants');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('twilio_conference_calls_participants', function (Blueprint $table) {
            $table->increments('id');
            $table->string('account_sid');
            $table->string('call_sid');
            $table->string('conference_sid');
            $table->string('participant_number');
            $table->string('status')->nullable();
            $table->integer('duration')->default(0);
            $table->timestamps();
        });
    }
}
