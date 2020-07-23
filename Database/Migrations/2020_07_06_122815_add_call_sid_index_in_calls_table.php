<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCallSidIndexInCallsTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('twilio_calls', function (Blueprint $table) {
            $table->dropIndex(['call_sid']);
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            Schema::table('twilio_calls', function (Blueprint $table) {
                $table->index(['call_sid']);
            });
        } catch (\Illuminate\Database\QueryException $e) {
        }
    }
}
