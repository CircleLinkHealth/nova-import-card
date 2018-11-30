<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTwilioFeatureToggleInCpmSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! Schema::hasColumn('cpm_settings', 'twilio_enabled')) {
            Schema::table('cpm_settings', function (Blueprint $table) {
                $table->boolean('twilio_enabled')->default(0);
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

        if (Schema::hasColumn('cpm_settings', 'twilio_enabled')) {
            Schema::table('cpm_settings', function (Blueprint $table) {
                $table->dropColumn('twilio_enabled');
            });
        }
    }
}
