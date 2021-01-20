<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeParticipantNumberNullableInTwilioConferenceCallsParticipantsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('twilio_conference_calls_participants', function (Blueprint $table) {
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('twilio_conference_calls_participants', function (Blueprint $table) {
            $table->string('participant_number')->default(null)->nullable(true)->change();
        });
    }
}
