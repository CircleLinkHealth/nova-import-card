<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToSnomedToIcd9MapTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('snomed_to_icd9_map', function (Blueprint $table) {
            $table->dropForeign('snomed_to_icd9_map_cpm_problem_id_foreign');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('snomed_to_icd9_map', function (Blueprint $table) {
            $table->foreign('cpm_problem_id')->references('id')->on('cpm_problems')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }
}
