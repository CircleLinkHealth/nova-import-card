<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePhoenixHeartInsurancesTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('phoenix_heart_insurances');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('phoenix_heart_insurances', function (Blueprint $table) {
            $table->integer('patient_id')->nullable();
            $table->integer('order')->nullable();
            $table->string('name')->nullable();
            $table->string('list_name')->nullable();
        });
    }
}
