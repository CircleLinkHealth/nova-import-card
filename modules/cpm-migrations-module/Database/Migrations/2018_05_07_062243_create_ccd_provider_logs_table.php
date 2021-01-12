<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCcdProviderLogsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('ccd_provider_logs');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('ccd_provider_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('ml_ignore');
            $table->integer('location_id')->unsigned()->nullable()->index('ccd_provider_logs_location_id_foreign');
            $table->integer('practice_id')->unsigned()->nullable()->index('ccd_provider_logs_practice_id_foreign');
            $table->integer('billing_provider_id')->unsigned()->nullable()->index('ccd_provider_logs_billing_provider_id_foreign');
            $table->integer('user_id')->unsigned()->nullable()->index('ccd_provider_logs_user_id_foreign');
            $table->string('medical_record_type')->nullable();
            $table->integer('medical_record_id')->unsigned()->nullable();
            $table->integer('vendor_id')->unsigned()->nullable()->index('ccd_provider_logs_vendor_id_foreign');
            $table->string('npi')->nullable();
            $table->string('provider_id')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('organization')->nullable();
            $table->string('street')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip', 5)->nullable();
            $table->string('cell_phone', 12)->nullable();
            $table->string('home_phone', 12)->nullable();
            $table->string('work_phone', 12)->nullable();
            $table->boolean('import');
            $table->boolean('invalid');
            $table->boolean('edited');
            $table->softDeletes();
            $table->timestamps();
        });
    }
}
