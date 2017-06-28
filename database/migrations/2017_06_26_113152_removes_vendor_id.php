<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemovesVendorId extends Migration
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
            try {
                Schema::table($t, function (Blueprint $table) {
//                    $table->dropForeign(['vendor_id']);
                    $table->unsignedInteger('vendor_id')
                        ->nullable()
                        ->change();
                });
            } catch (\Exception $e) {
                echo $e;
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
        //
    }
}
