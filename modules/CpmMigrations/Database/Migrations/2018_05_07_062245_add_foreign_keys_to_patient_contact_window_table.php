<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToPatientContactWindowTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('patient_contact_window', function (Blueprint $table) {
            $table->dropForeign('patient_contact_window_patient_info_id_foreign');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('patient_contact_window', function (Blueprint $table) {
            $table->foreign('patient_info_id')->references('id')->on('patient_info')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }
}
