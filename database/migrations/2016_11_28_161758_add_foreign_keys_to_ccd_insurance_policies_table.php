<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCcdInsurancePoliciesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ccd_insurance_policies', function (Blueprint $table) {
            $table->foreign('ccda_id')->references('id')->on('ccdas')->onUpdate('CASCADE')->onDelete('CASCADE');
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
        Schema::table('ccd_insurance_policies', function (Blueprint $table) {
            $table->dropForeign('ccd_insurance_policies_ccda_id_foreign');
            $table->dropForeign('ccd_insurance_policies_patient_id_foreign');
        });
    }
}
