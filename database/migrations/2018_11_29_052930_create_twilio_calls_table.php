<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTwilioCallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('twilio_calls', function (Blueprint $table) {
            $table->increments('id');
            $table->string('call_sid')->nullable();
            $table->string('call_status')->nullable();
            $table->string('from')->nullable();
            $table->string('to')->nullable();
            $table->integer('inbound_user_id')->nullable();
            $table->integer('outbound_user_id')->nullable();
            $table->integer('duration')->default(0);
            $table->integer('call_duration')->default(0);
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
        Schema::dropIfExists('twilio_calls');
    }
}
