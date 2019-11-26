<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToAllergyImportsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('allergy_imports', function (Blueprint $table) {
            $table->dropForeign('allergy_imports_ccd_allergy_log_id_foreign');
            $table->dropForeign('allergy_imports_imported_medical_record_id_foreign');
            $table->dropForeign('allergy_imports_substitute_id_foreign');
            $table->dropForeign('allergy_imports_vendor_id_foreign');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('allergy_imports', function (Blueprint $table) {
            $table->foreign('ccd_allergy_log_id')->references('id')->on('ccd_allergy_logs')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('imported_medical_record_id')->references('id')->on('imported_medical_records')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('substitute_id')->references('id')->on('allergy_imports')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('vendor_id')->references('id')->on('ccd_vendors')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }
}
