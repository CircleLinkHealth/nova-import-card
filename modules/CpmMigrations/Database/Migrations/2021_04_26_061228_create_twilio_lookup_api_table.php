<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTwilioLookupApiTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('twilio_lookup_api');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('twilio_lookup_api', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number');
            $table->boolean('is_mobile')->nullable();
            $table->string('carrier')->nullable();
            $table->string('api_error_code')->nullable();
            $table->string('api_error_details')->nullable();
            $table->timestamps();

            $table->unique('phone_number');
        });
    }
}
