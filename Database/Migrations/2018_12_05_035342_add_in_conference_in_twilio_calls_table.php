<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInConferenceInTwilioCallsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('twilio_calls', function (Blueprint $table) {
            $table->dropColumn('in_conference');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('twilio_calls', function (Blueprint $table) {
            $table->boolean('in_conference')->default(false);
        });
    }
}
