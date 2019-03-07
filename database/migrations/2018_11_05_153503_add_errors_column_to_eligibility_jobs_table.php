<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddErrorsColumnToEligibilityJobsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('eligibility_jobs', function (Blueprint $table) {
            $table->dropColumn('errors');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('eligibility_jobs', function (Blueprint $table) {
            $table->json('errors')->after('messages')->nullable();
        });
    }
}
