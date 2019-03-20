<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTwilioRecordingsFeatureToggleInCpmSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! Schema::hasColumn('cpm_settings', 'twilio_recordings_enabled')) {
            Schema::table('cpm_settings', function (Blueprint $table) {
                $table->boolean('twilio_recordings_enabled')->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('cpm_settings', 'twilio_recordings_enabled')) {
            Schema::table('cpm_settings', function (Blueprint $table) {
                $table->dropColumn('twilio_recordings_enabled');
            });
        }
    }
}
