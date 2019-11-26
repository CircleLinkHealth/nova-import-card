<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCcdMedicationLogsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('ccd_medication_logs');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('ccd_medication_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('medical_record_type')->nullable();
            $table->integer('medical_record_id')->unsigned()->nullable();
            $table->integer('vendor_id')->unsigned()->nullable()->index('ccd_medication_logs_vendor_id_foreign');
            $table->string('reference')->nullable();
            $table->string('reference_title')->nullable();
            $table->string('reference_sig')->nullable();
            $table->string('start')->nullable();
            $table->string('end')->nullable();
            $table->string('status')->nullable();
            $table->string('text')->nullable();
            $table->string('product_name')->nullable();
            $table->string('product_code')->nullable();
            $table->string('product_code_system')->nullable();
            $table->string('product_text')->nullable();
            $table->string('translation_name')->nullable();
            $table->string('translation_code')->nullable();
            $table->string('translation_code_system')->nullable();
            $table->string('translation_code_system_name')->nullable();
            $table->boolean('import');
            $table->boolean('invalid');
            $table->boolean('edited');
            $table->softDeletes();
            $table->timestamps();
        });
    }
}
