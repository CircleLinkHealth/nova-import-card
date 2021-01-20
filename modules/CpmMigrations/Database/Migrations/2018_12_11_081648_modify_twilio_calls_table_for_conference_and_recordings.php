<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyTwilioCallsTableForConferenceAndRecordings extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table(
            'twilio_calls',
            function (Blueprint $table) {
                $table->renameColumn('dial_conference_duration', 'dial_call_duration');

                $table->integer('sequence_number')->nullable();

                $table->string('recording_duration')->default(0);
                $table->string('recording_url')->nullable();

                $table->renameColumn('dial_recording_sid', 'recording_sid');

                $table->dropColumn(
                    [
                        'conference_recording_sid',
                        'conference_sid',
                        'conference_status',
                        'conference_friendly_name',
                    ]
                );
            }
        );
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table(
            'twilio_calls',
            function (Blueprint $table) {
                if (Schema::hasColumn('twilio_calls', 'dial_call_duration')) {
                    $table->renameColumn('dial_call_duration', 'dial_conference_duration');
                }
            }
        );

        Schema::table(
            'twilio_calls',
            function (Blueprint $table) {
                //this field is not needed because each record in this table is an aggregation of different
                //callbacks which have different sequence_number(s)
                if (Schema::hasColumn('twilio_calls', 'sequence_number')) {
                    $table->dropColumn('sequence_number');
                }
            }
        );

        Schema::table(
            'twilio_calls',
            function (Blueprint $table) {
                if (Schema::hasColumn('twilio_calls', 'recording_duration') && Schema::hasColumn(
                    'twilio_calls',
                    'recording_url'
                )) {
                    //need dial_recording_sid and conference_recording_sid
                    $table->dropColumn(['recording_duration', 'recording_url']);
                }
            }
        );

        Schema::table(
            'twilio_calls',
            function (Blueprint $table) {
                if (Schema::hasColumn('twilio_calls', 'recording_sid')) {
                    $table->renameColumn('recording_sid', 'dial_recording_sid');
                }
            }
        );

        Schema::table(
            'twilio_calls',
            function (Blueprint $table) {
                $table->string('conference_recording_sid')->nullable();

                //conference related
                $table->string('conference_sid')->nullable();
                $table->string('conference_status')->nullable();
                $table->string('conference_friendly_name')->nullable();
            }
        );
    }
}
