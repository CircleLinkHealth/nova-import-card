<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddToledoTables extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('toledo-clinic_medications');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('toledo-clinic_medications', function (Blueprint $table) {
            $table->unsignedBigInteger('patient_id')->index();
            $table->string('name')->nullable();
            $table->string('sig')->nullable();
            $table->date('start')->nullable();
            $table->date('stop')->nullable();
            $table->string('status')->nullable()->index();
            $table->unsignedBigInteger('id')->autoIncrement();
            $table->timestamps();
        });
    }
}
