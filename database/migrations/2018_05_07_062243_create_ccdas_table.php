<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCcdasTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('ccdas');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('ccdas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('batch_id')->unsigned()->nullable()->index('ccdas_batch_id_foreign');
            $table->dateTime('date')->nullable();
            $table->string('mrn')->nullable();
            $table->string('referring_provider_name')->nullable();
            $table->integer('location_id')->unsigned()->nullable()->index('ccdas_location_id_foreign');
            $table->integer('practice_id')->unsigned()->nullable()->index('ccdas_practice_id_foreign');
            $table->integer('billing_provider_id')->unsigned()->nullable()->index('ccdas_billing_provider_id_foreign');
            $table->integer('user_id')->unsigned()->nullable()->index('lv_ccdas_user_id_foreign');
            $table->integer('patient_id')->unsigned()->nullable()->index('users_patient_id_foreign');
            $table->integer('vendor_id')->unsigned()->index('lv_ccdas_vendor_id_foreign');
            $table->string('source');
            $table->boolean('imported');
            $table->text('xml');
            $table->text('json')->nullable();
            $table->enum('status', ['determine_enrollement_eligibility', 'eligible', 'ineligible', 'patient_consented', 'patient_declined', 'import', 'qa', 'careplan_created'])->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
