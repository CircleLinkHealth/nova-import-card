<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMedicationImportsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('medication_imports');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('medication_imports', function (Blueprint $table) {
            $table->increments('id');
            $table->string('medical_record_type')->nullable();
            $table->integer('medical_record_id')->unsigned()->nullable();
            $table->integer('imported_medical_record_id')->unsigned()->index('medication_imports_imported_medical_record_id_foreign');
            $table->integer('vendor_id')->unsigned()->nullable()->index('medication_imports_vendor_id_foreign');
            $table->integer('ccd_medication_log_id')->unsigned()->index('medication_imports_ccd_medication_log_id_foreign');
            $table->integer('medication_group_id')->unsigned()->nullable()->index('medication_imports_medication_group_id_foreign');
            $table->string('name')->nullable();
            $table->string('sig')->nullable();
            $table->string('code')->nullable();
            $table->string('code_system')->nullable();
            $table->string('code_system_name')->nullable();
            $table->integer('substitute_id')->unsigned()->nullable()->index('medication_imports_substitute_id_foreign');
            $table->softDeletes();
            $table->timestamps();
        });
    }
}
