<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToImportedMedicalRecordsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('imported_medical_records', function (Blueprint $table) {
            $table->dropForeign('imported_medical_records_billing_provider_id_foreign');
            $table->dropForeign('imported_medical_records_duplicate_id_foreign');
            $table->dropForeign('imported_medical_records_location_id_foreign');
            $table->dropForeign('imported_medical_records_patient_id_foreign');
            $table->dropForeign('imported_medical_records_practice_id_foreign');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('imported_medical_records', function (Blueprint $table) {
            $table->foreign('billing_provider_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('duplicate_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign('location_id')->references('id')->on('locations')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('patient_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('practice_id')->references('id')->on('practices')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }
}
