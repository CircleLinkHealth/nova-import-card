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

            //need dial_recording_sid and conference_recording_sid
            $table->dropColumn(['recording_duration', 'recording_url']);
            $table->renameColumn('recording_sid', 'dial_recording_sid');
            $table->string('conference_recording_sid')->nullable();

            //conference related
            $table->string('conference_sid')->nullable();
            $table->string('conference_status')->nullable();
            $table->string('conference_duration')->default(0);
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

            $table->string('recording_duration')->nullable();
            $table->string('recording_url')->nullable();

            $table->renameColumn('dial_recording_sid', 'recording_sid');

            $table->dropColumn([
                'conference_recording_sid',
                'conference_sid',
                'conference_status',
                'conference_duration',
            ]);
        });
    }
}
