<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCcdaRequestsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('ccda_requests');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('ccda_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ccda_id')->unsigned()->nullable()->index('ccda_requests_ccda_id_foreign');
            $table->string('vendor');
            $table->integer('patient_id')->unsigned();
            $table->integer('department_id')->unsigned();
            $table->integer('practice_id')->unsigned();
            $table->boolean('successful_call')->nullable();
            $table->integer('document_id')->unsigned()->nullable();
            $table->timestamps();
            $table->unique(['vendor', 'patient_id']);
        });
    }
}
