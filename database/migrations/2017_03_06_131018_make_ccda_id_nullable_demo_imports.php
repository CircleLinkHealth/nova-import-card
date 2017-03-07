<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeCcdaIdNullableDemoImports extends Migration
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
            'ccd_allergy_logs',
            'ccd_demographics_logs',
            'ccd_document_logs',
            'ccd_medication_logs',
            'ccd_problem_logs',
            'ccd_provider_logs',
            'ccd_insurance_policies',
        ];

        foreach ($tables as $t) {
            try {
                Schema::table($t, function (Blueprint $table) {
                    $table->dropColumn('ccda_id');

                    $table->dropForeign(['ccda_id']);
                });
            } catch (\Exception $e) {
                echo $e->getMessage() . "\n\n\n";
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('demographics_imports', function (Blueprint $table) {
            //
        });
    }
}
