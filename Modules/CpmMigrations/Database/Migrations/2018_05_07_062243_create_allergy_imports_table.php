<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAllergyImportsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('allergy_imports');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('allergy_imports', function (Blueprint $table) {
            $table->increments('id');
            $table->string('medical_record_type')->nullable();
            $table->integer('medical_record_id')->unsigned()->nullable();
            $table->integer('imported_medical_record_id')->unsigned()->index('allergy_imports_imported_medical_record_id_foreign');
            $table->integer('vendor_id')->unsigned()->nullable()->index('allergy_imports_vendor_id_foreign');
            $table->integer('ccd_allergy_log_id')->unsigned()->index('allergy_imports_ccd_allergy_log_id_foreign');
            $table->string('allergen_name')->nullable();
            $table->integer('substitute_id')->unsigned()->nullable()->index('allergy_imports_substitute_id_foreign');
            $table->softDeletes();
            $table->timestamps();
        });
    }
}
