<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImportedMedicalRecordsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('imported_medical_records');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('imported_medical_records', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('patient_id')->unsigned()->nullable()->index('imported_medical_records_patient_id_foreign');
            $table->string('medical_record_type');
            $table->integer('medical_record_id')->unsigned();
            $table->integer('billing_provider_id')->unsigned()->nullable()->index('imported_medical_records_billing_provider_id_foreign');
            $table->integer('location_id')->unsigned()->nullable()->index('imported_medical_records_location_id_foreign');
            $table->integer('practice_id')->unsigned()->nullable()->index('imported_medical_records_practice_id_foreign');
            $table->integer('duplicate_id')->unsigned()->nullable()->index('imported_medical_records_duplicate_id_foreign');
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
