<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCcdAllergiesTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('ccd_allergies', function (Blueprint $table) {
            $table->dropForeign('ccd_allergies_allergy_import_id_foreign');
            $table->dropForeign('ccd_allergies_patient_id_foreign');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('ccd_allergies', function (Blueprint $table) {
            $table->foreign('allergy_import_id')->references('id')->on('allergy_imports')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign('patient_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }
}
