<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHealthRecordTypeAndIdToItemLogTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tables = [
            'ccd_allergy_logs',
            'ccd_demographics_logs',
            'ccd_document_logs',
            'ccd_medication_logs',
            'ccd_problem_logs',
            'ccd_provider_logs',

            'allergy_imports',
            'demographics_imports',
            'medication_imports',
            'problem_imports',
        ];

        foreach ($tables as $t) {
            Schema::table($t, function (Blueprint $table) {
                $table->unsignedInteger('health_record_id')
                    ->nullable()
                    ->after('id');
                $table->string('health_record_type')
                    ->nullable()
                    ->after('id');
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
            'ccd_allergy_logs',
            'ccd_demographics_logs',
            'ccd_document_logs',
            'ccd_medication_logs',
            'ccd_problem_logs',
            'ccd_provider_logs',

            'allergy_imports',
            'demographics_imports',
            'medication_imports',
            'problem_imports',
        ];

        foreach ($tables as $t) {
            Schema::table($t, function (Blueprint $table) {
                $table->dropColumn('health_record_id');
                $table->dropColumn('health_record_type');
            });
        }
    }
}
