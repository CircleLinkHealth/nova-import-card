<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddsImportedMedicalRecordForeign extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tables = [
            'allergy_imports',
            'demographics_imports',
            'medication_imports',
            'problem_imports',
        ];

        foreach ($tables as $t) {
            Schema::table($t, function (Blueprint $table) {
                $table->unsignedInteger('imported_medical_record_id')
                    ->after('medical_record_id');

                $table->foreign('imported_medical_record_id')
                    ->references('id')
                    ->on('imported_medical_records')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tables = [
            'allergy_imports',
            'demographics_imports',
            'medication_imports',
            'problem_imports',
        ];

        foreach ($tables as $t) {
            Schema::table($t, function (Blueprint $table) {
                $table->dropForeign(['imported_medical_record_id']);
                $table->dropColumn('imported_medical_record_id');
            });
        }
    }
}
