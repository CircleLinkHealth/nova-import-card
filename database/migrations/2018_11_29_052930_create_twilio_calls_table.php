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
            $table->string('application_sid')->nullable();
            $table->string('account_sid')->nullable();
            $table->string('call_status')->nullable();
            $table->string('direction')->nullable();
            $table->string('from')->nullable();
            $table->string('to')->nullable();
            $table->boolean('is_unlisted_number')->default(false);
            $table->integer('inbound_user_id')->nullable();
            $table->integer('outbound_user_id')->nullable();
            $table->integer('call_duration')->default(0);
            $table->string('recording_sid')->nullable();
            $table->integer('recording_duration')->default(0);
            $table->string('recording_url')->nullable();
            $table->integer('sequence_number')->nullable();
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
