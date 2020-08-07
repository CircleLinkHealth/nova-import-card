<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class AddCaLoadingTimeToEnrollees extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\Artisan::call('ca:unassignedTimeToEnrollees');
    }
}
