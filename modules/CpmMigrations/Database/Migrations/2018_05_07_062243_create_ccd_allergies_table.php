<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCcdAllergiesTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('ccd_allergies');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('ccd_allergies', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('allergy_import_id')->unsigned()->nullable()->index('ccd_allergies_allergy_import_id_foreign');
            $table->integer('ccda_id')->nullable()->index('ccd_allergies_ccda_id_foreign');
            $table->integer('patient_id')->unsigned()->index('ccd_allergies_patient_id_foreign');
            $table->integer('vendor_id')->nullable()->index('ccd_allergies_vendor_id_foreign');
            $table->integer('ccd_allergy_log_id')->nullable()->index('ccd_allergies_ccd_allergy_log_id_foreign');
            $table->text('allergen_name', 65535)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }
}
