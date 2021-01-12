<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCcdDocumentLogsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('ccd_document_logs');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('ccd_document_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('ml_ignore');
            $table->integer('location_id')->unsigned()->nullable()->index('ccd_document_logs_location_id_foreign');
            $table->integer('practice_id')->unsigned()->nullable()->index('ccd_document_logs_practice_id_foreign');
            $table->integer('billing_provider_id')->unsigned()->nullable()->index('ccd_document_logs_billing_provider_id_foreign');
            $table->string('medical_record_type')->nullable();
            $table->integer('medical_record_id')->unsigned()->nullable();
            $table->integer('vendor_id')->unsigned()->nullable()->index('ccd_document_logs_vendor_id_foreign');
            $table->string('type');
            $table->string('custodian');
            $table->boolean('import');
            $table->boolean('invalid');
            $table->boolean('edited');
            $table->softDeletes();
            $table->timestamps();
        });
    }
}
