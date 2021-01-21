<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTwilioDebuggerLogsTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('twilio_debugger_logs');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('twilio_debugger_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('sid');
            $table->string('account_sid');
            $table->string('parent_account_sid');
            $table->string('level');
            $table->json('payload');
            $table->timestamp('event_timestamp');

            $table->timestamps();
        });
    }
}
