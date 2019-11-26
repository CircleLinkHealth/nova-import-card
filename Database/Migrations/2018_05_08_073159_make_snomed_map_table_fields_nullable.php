<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeSnomedMapTableFieldsNullable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('snomed_to_cpm_icd_maps', function (Blueprint $table) {
            $table->string('snomed_name')->change();
            $table->string('icd_9_code')->change();
            $table->string('icd_9_name')->change();
            $table->float('icd_9_avg_usage', 10, 0)->change();
            $table->boolean('icd_9_is_nec')->change();
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('snomed_to_cpm_icd_maps', function (Blueprint $table) {
            $table->string('snomed_name')->nullable()->change();
            $table->string('icd_9_code')->nullable()->change();
            $table->string('icd_9_name')->nullable()->change();
            $table->float('icd_9_avg_usage', 10, 0)->nullable()->change();
            $table->boolean('icd_9_is_nec')->nullable()->change();
        });
    }
}
