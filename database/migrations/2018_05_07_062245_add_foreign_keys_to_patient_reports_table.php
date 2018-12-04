<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToPatientReportsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_reports', function (Blueprint $table) {
            $table->foreign('location_id')->references('id')->on('locations')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('patient_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_reports', function (Blueprint $table) {
            $table->dropForeign('patient_reports_location_id_foreign');
            $table->dropForeign('patient_reports_patient_id_foreign');
        });
    }
}
