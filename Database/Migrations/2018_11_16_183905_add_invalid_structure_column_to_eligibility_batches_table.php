<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInvalidStructureColumnToEligibilityBatchesTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('eligibility_jobs', function (Blueprint $table) {
            $table->dropColumn('invalid_data');
            $table->dropColumn('invalid_structure');
            $table->dropColumn('invalid_mrn');
            $table->dropColumn('invalid_first_name');
            $table->dropColumn('invalid_last_name');
            $table->dropColumn('invalid_dob');
            $table->dropColumn('invalid_problems');
            $table->dropColumn('invalid_phones');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('eligibility_jobs', function (Blueprint $table) {
            $table->boolean('invalid_data')->default(0);
            $table->boolean('invalid_structure')->default(0);
            $table->boolean('invalid_mrn')->default(0);
            $table->boolean('invalid_first_name')->default(0);
            $table->boolean('invalid_last_name')->default(0);
            $table->boolean('invalid_dob')->default(0);
            $table->boolean('invalid_problems')->default(0);
            $table->boolean('invalid_phones')->default(0);
        });
    }
}
