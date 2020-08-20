<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocationProblemServicesTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('location_problem_services');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('location_problem_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('location_id');
            $table->unsignedInteger('cpm_problem_id');
            $table->unsignedInteger('chargeable_service_id');
            $table->timestamps();

            $table->foreign('location_id')
                ->references('id')
                ->on('locations')
                ->onDelete('cascade');

            $table->foreign('cpm_problem_id')
                ->references('id')
                ->on('cpm_problems')
                ->onDelete('cascade');

            $table->foreign('chargeable_service_id')
                ->references('id')
                ->on('chargeable_services')
                ->onDelete('cascade');
        });
    }
}
