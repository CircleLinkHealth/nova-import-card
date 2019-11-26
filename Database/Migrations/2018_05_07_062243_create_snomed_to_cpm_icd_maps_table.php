<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSnomedToCpmIcdMapsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('snomed_to_cpm_icd_maps');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('snomed_to_cpm_icd_maps', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('snomed_code')->nullable();
            $table->string('snomed_name')->nullable();
            $table->string('icd_10_code')->nullable();
            $table->string('icd_10_name')->nullable();
            $table->timestamps();
            $table->string('icd_9_code')->nullable();
            $table->string('icd_9_name')->nullable();
            $table->float('icd_9_avg_usage', 10, 0)->nullable();
            $table->boolean('icd_9_is_nec')->nullable();
            $table->integer('cpm_problem_id')->unsigned()->nullable()->index('snomed_to_cpm_icd_maps_cpm_problem_id_foreign');
        });
    }
}
