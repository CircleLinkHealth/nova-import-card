<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCcdMedicationsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('ccd_medications');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('ccd_medications', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('medication_import_id')->unsigned()->nullable()->index('ccd_medications_medication_import_id_foreign');
            $table->integer('ccda_id')->nullable()->index('ccd_medications_ccda_id_foreign');
            $table->integer('patient_id')->unsigned()->index('ccd_medications_patient_id_foreign');
            $table->integer('vendor_id')->nullable()->index('ccd_medications_vendor_id_foreign');
            $table->integer('ccd_medication_log_id')->nullable()->index('ccd_medications_ccd_medication_log_id_foreign');
            $table->integer('medication_group_id')->nullable()->index('medication_group_foreign');
            $table->text('name', 65535)->nullable();
            $table->string('sig')->nullable();
            $table->string('code')->nullable();
            $table->string('code_system')->nullable();
            $table->string('code_system_name')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }
}
