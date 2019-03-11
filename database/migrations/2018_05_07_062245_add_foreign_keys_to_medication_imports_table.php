<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToMedicationImportsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('medication_imports', function (Blueprint $table) {
            $table->dropForeign('medication_imports_ccd_medication_log_id_foreign');
            $table->dropForeign('medication_imports_imported_medical_record_id_foreign');
            $table->dropForeign('medication_imports_medication_group_id_foreign');
            $table->dropForeign('medication_imports_substitute_id_foreign');
            $table->dropForeign('medication_imports_vendor_id_foreign');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('medication_imports', function (Blueprint $table) {
            $table->foreign('ccd_medication_log_id')->references('id')->on('ccd_medication_logs')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('imported_medical_record_id')->references('id')->on('imported_medical_records')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('medication_group_id')->references('id')->on('cpm_medication_groups')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('substitute_id')->references('id')->on('medication_imports')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('vendor_id')->references('id')->on('ccd_vendors')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }
}
