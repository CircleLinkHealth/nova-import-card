<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSnomedToIcd9MapTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('snomed_to_icd9_map');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('snomed_to_icd9_map', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('ccm_eligible');
            $table->string('code');
            $table->string('name');
            $table->float('avg_usage', 10, 0);
            $table->boolean('is_nec');
            $table->integer('snomed_code');
            $table->string('snomed_name');
            $table->integer('cpm_problem_id')->unsigned()->nullable()->index('snomed_to_icd9_map_cpm_problem_id_foreign');
        });
    }
}
