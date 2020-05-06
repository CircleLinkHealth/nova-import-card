<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddPracticePullTables extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('practice_pull_medications');
        Schema::dropIfExists('practice_pull_problems');
        Schema::dropIfExists('practice_pull_demographics');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Medications
        Schema::create('practice_pull_medications', function (Blueprint $table) {
            $table->string('mrn')->index();
            $table->string('name')->nullable();
            $table->string('sig')->nullable();
            $table->date('start')->nullable();
            $table->date('stop')->nullable();
            $table->string('status')->nullable()->index();
            $table->unsignedBigInteger('id')->autoIncrement();
            $table->unsignedInteger('location_id')->nullable();
            $table->unsignedInteger('billing_provider_user_id')->nullable();
            $table->unsignedInteger('practice_id')->index();
            $table->timestamps();
        });

        Schema::table('practice_pull_medications', function (Blueprint $table) {
            $table->foreign('billing_provider_user_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('location_id')->references('id')->on('locations')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('practice_id')->references('id')->on('practices')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        //Problems
        Schema::create('practice_pull_problems', function (Blueprint $table) {
            $table->string('mrn')->index();
            $table->string('name')->nullable()->index();
            $table->string('code')->nullable()->index();
            $table->string('code_type')->nullable();
            $table->date('start')->nullable();
            $table->date('stop')->nullable();
            $table->string('status')->nullable()->index();
            $table->unsignedBigInteger('id')->autoIncrement();
            $table->unsignedInteger('location_id')->nullable();
            $table->unsignedInteger('billing_provider_user_id')->nullable();
            $table->unsignedInteger('practice_id')->index();
            $table->timestamps();
        });

        Schema::table('practice_pull_problems', function (Blueprint $table) {
            $table->foreign('billing_provider_user_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('location_id')->references('id')->on('locations')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('practice_id')->references('id')->on('practices')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        //Demographics
        Schema::create('practice_pull_demographics', function (Blueprint $table) {
            $table->string('mrn')->index();
            $table->string('first_name')->nullable()->index();
            $table->string('last_name')->nullable()->index();
            $table->date('dob')->nullable()->index();
            $table->date('last_encounter')->nullable()->index();
            $table->string('gender')->nullable();
            $table->string('lang')->nullable();
            $table->string('referring_provider_name')->nullable()->index();
            $table->string('cell_phone')->nullable();
            $table->string('home_phone')->nullable();
            $table->string('other_phone')->nullable();
            $table->string('primary_phone')->nullable();
            $table->string('email')->nullable();
            $table->string('street')->nullable();
            $table->string('street2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->string('primary_insurance')->nullable();
            $table->string('secondary_insurance')->nullable();
            $table->string('tertiary_insurance')->nullable();

            $table->unsignedBigInteger('id')->autoIncrement();
            $table->unsignedInteger('location_id')->nullable();
            $table->unsignedInteger('billing_provider_user_id')->nullable();
            $table->unsignedInteger('practice_id')->index();
            $table->timestamps();
        });

        Schema::table('practice_pull_demographics', function (Blueprint $table) {
            $table->foreign('billing_provider_user_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('location_id')->references('id')->on('locations')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('practice_id')->references('id')->on('practices')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }
}
