<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTwilioRawLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('twilio_raw_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('call_sid')->nullable();
            $table->string('application_sid')->nullable();
            $table->string('account_sid')->nullable();
            $table->string('call_status')->nullable();
            $table->json('log')->nullable();
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
        Schema::dropIfExists('twilio_raw_logs');
    }
}
