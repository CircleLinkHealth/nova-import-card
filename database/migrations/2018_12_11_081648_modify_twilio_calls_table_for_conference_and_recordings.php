<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyTwilioCallsTableForConferenceAndRecordings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('twilio_calls', function (Blueprint $table) {

            $table->renameColumn('dial_call_duration', 'dial_conference_duration');

            //this field is not needed because each record in this table is an aggregation of different
            //callbacks which have different sequence_number(s)
            $table->dropColumn('sequence_number');

            //need dial_recording_sid and conference_recording_sid
            $table->dropColumn(['recording_duration', 'recording_url']);
            $table->renameColumn('recording_sid', 'dial_recording_sid');
            $table->string('conference_recording_sid')->nullable();

            //conference related
            $table->string('conference_sid')->nullable();
            $table->string('conference_status')->nullable();
            $table->string('conference_friendly_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('twilio_calls', function (Blueprint $table) {

            $table->renameColumn('dial_conference_duration', 'dial_call_duration');

            $table->integer('sequence_number')->nullable();

            $table->string('recording_duration')->default(0);
            $table->string('recording_url')->nullable();

            $table->renameColumn('dial_recording_sid', 'recording_sid');

            $table->dropColumn([
                'conference_recording_sid',
                'conference_sid',
                'conference_status',
                'conference_friendly_name',
            ]);
        });
    }
}
