<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDemographicsImportsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('demographics_imports');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('demographics_imports', function (Blueprint $table) {
            $table->increments('id');
            $table->string('medical_record_type')->nullable();
            $table->integer('medical_record_id')->unsigned()->nullable();
            $table->integer('imported_medical_record_id')->unsigned()->index('demographics_imports_imported_medical_record_id_foreign');
            $table->integer('vendor_id')->unsigned()->nullable()->index('demographics_imports_vendor_id_foreign');
            $table->integer('program_id')->unsigned()->nullable()->index('demographics_imports_program_id_foreign');
            $table->integer('provider_id')->unsigned()->nullable()->index('demographics_imports_provider_id_foreign');
            $table->integer('location_id')->unsigned()->nullable()->index('demographics_imports_location_id_foreign');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('dob')->nullable();
            $table->string('gender')->nullable();
            $table->string('mrn_number')->nullable();
            $table->string('street')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip', 5)->nullable();
            $table->string('primary_phone')->nullable();
            $table->string('cell_phone', 12)->nullable();
            $table->string('home_phone', 12)->nullable();
            $table->string('work_phone', 12)->nullable();
            $table->string('email')->nullable();
            $table->string('preferred_contact_timezone')->nullable();
            $table->string('consent_date')->nullable();
            $table->string('preferred_contact_language')->nullable();
            $table->string('study_phone_number')->nullable();
            $table->integer('substitute_id')->unsigned()->nullable()->index('demographics_imports_substitute_id_foreign');
            $table->timestamps();
            $table->string('preferred_call_times')->nullable();
            $table->string('preferred_call_days')->nullable();
        });
    }
}
