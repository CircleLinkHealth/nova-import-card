<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTwilioFeatureToggleInCpmSettings extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (Schema::hasColumn('cpm_settings', 'twilio_enabled')) {
            Schema::table('cpm_settings', function (Blueprint $table) {
                $table->dropColumn('twilio_enabled');
            });
        }
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        if ( ! Schema::hasColumn('cpm_settings', 'twilio_enabled')) {
            Schema::table('cpm_settings', function (Blueprint $table) {
                $table->boolean('twilio_enabled')->default(0);
            });
        }
    }
}
