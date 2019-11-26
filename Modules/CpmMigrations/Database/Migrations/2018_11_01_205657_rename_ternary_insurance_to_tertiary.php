<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameTernaryInsuranceToTertiary extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('eligibility_jobs', function (Blueprint $table) {
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        if (Schema::hasColumn('eligibility_jobs', 'ternary_insurance')) {
            Schema::table('eligibility_jobs', function (Blueprint $table) {
                $table->renameColumn('ternary_insurance', 'tertiary_insurance');
            });
        }
    }
}
