<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePhoenixHeartMedicationsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('phoenix_heart_medications');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('phoenix_heart_medications', function (Blueprint $table) {
            $table->integer('patient_id')->nullable();
            $table->string('description')->nullable();
            $table->string('instructions')->nullable();
            $table->string('start_date')->nullable();
            $table->string('end_date')->nullable();
            $table->string('stop_reason')->nullable();
        });
    }
}
