<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Customer\Database\Seeders\TaskRecommendationsSeeder;
use Illuminate\Database\Migrations\Migration;

class MoveScreeningsInPppTaskRecommendations extends Migration
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
        TaskRecommendationsSeeder::run(); // its updateOrInsert
    }
}
