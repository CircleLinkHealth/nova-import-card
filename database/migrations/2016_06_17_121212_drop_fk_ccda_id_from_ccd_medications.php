<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropFkCcdaIdFromCcdMedications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ccd_medications', function ($table) {
            /*
            $table->dropForeign('ccd_medications_ccda_id_foreign');
            $table->dropForeign('ccd_medications_ccd_medication_log_id_foreign');
            $table->dropForeign('ccd_medications_vendor_id_foreign');
            $table->dropForeign('medication_group_foreign');
            */
        });
        Schema::table('ccd_medications', function ($table) {
            $table->integer('ccda_id')->nullable()->change();
            $table->integer('ccd_medication_log_id')->nullable()->change();
            $table->integer('vendor_id')->nullable()->change();
            $table->integer('medication_group_id')->nullable()->change();
        });

        Schema::table('ccd_problems', function ($table) {
            /*
            $table->dropForeign('ccd_problems_ccda_id_foreign');
            $table->dropForeign('ccd_problems_ccd_problem_log_id_foreign');
            $table->dropForeign('ccd_problems_vendor_id_foreign');
            $table->dropForeign('ccd_problems_cpm_problem_id_foreign');
            */
        });
        Schema::table('ccd_problems', function ($table) {
            $table->integer('ccda_id')->nullable()->change();
            $table->integer('ccd_problem_log_id')->nullable()->change();
            $table->integer('vendor_id')->nullable()->change();
            $table->integer('cpm_problem_id')->nullable()->change();
        });

        Schema::table('ccd_allergies', function ($table) {
            $table->dropForeign('ccd_allergies_ccda_id_foreign');
            $table->dropForeign('ccd_allergies_ccd_allergy_log_id_foreign');
            $table->dropForeign('ccd_allergies_vendor_id_foreign');
        });
        Schema::table('ccd_allergies', function ($table) {
            $table->integer('ccda_id')->nullable()->change();
            $table->integer('ccd_allergy_log_id')->nullable()->change();
            $table->integer('vendor_id')->nullable()->change();
        });
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
