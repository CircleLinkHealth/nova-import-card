<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTwilioConferenceCallsParticipantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('twilio_conference_calls_participants', function (Blueprint $table) {
            $table->increments('id');
            $table->string('account_sid');
            $table->string('call_sid');
            $table->string('conference_sid');
            $table->string('participant_number');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('twilio_conference_calls_participants');
    }
}
