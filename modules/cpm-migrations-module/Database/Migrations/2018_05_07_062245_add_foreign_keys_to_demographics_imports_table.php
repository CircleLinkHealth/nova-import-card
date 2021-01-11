<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToDemographicsImportsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('demographics_imports', function (Blueprint $table) {
            $table->dropForeign('demographics_imports_imported_medical_record_id_foreign');
            $table->dropForeign('demographics_imports_location_id_foreign');
            $table->dropForeign('demographics_imports_program_id_foreign');
            $table->dropForeign('demographics_imports_provider_id_foreign');
            $table->dropForeign('demographics_imports_substitute_id_foreign');
            $table->dropForeign('demographics_imports_vendor_id_foreign');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('demographics_imports', function (Blueprint $table) {
            $table->foreign('imported_medical_record_id')->references('id')->on('imported_medical_records')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('location_id')->references('id')->on('locations')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('program_id')->references('id')->on('practices')->onUpdate('CASCADE')->onDelete('RESTRICT');
            $table->foreign('provider_id')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('substitute_id')->references('id')->on('demographics_imports')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('vendor_id')->references('id')->on('ccd_vendors')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }
}
