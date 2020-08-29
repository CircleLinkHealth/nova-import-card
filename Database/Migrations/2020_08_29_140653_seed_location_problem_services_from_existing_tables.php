<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\CcmBilling\Database\Seeders\CpmProblemChargeableServiceLocationSeederTableSeeder;
use Illuminate\Database\Migrations\Migration;

class SeedLocationProblemServicesFromExistingTables extends Migration
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
        Artisan::call('db:seed', [
            '--class' => CpmProblemChargeableServiceLocationSeederTableSeeder::class,
        ]);
    }
}
