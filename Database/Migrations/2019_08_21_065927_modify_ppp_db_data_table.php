<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Customer\Database\Seeders\TaskRecommendationsSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyPppDbDataTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        if (Schema::hasColumns('ppp_task_recommendations', ['codes', 'rec_task_titles'])) {
            Schema::table('ppp_task_recommendations', function (Blueprint $table) {
                $table->dropColumn('codes', 'rec_task_titles');
            });
        }

        TaskRecommendationsSeeder::run();
    }
}
