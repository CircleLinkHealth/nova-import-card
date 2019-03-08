<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCcdMedicationsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('ccd_medications', function (Blueprint $table) {
            $table->dropForeign('ccd_medications_medication_import_id_foreign');
            $table->dropForeign('ccd_medications_patient_id_foreign');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('ccd_medications', function (Blueprint $table) {
            $table->foreign('medication_import_id')->references('id')->on('medication_imports')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign('patient_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }
}
