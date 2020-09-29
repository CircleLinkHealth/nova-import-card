<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\CcmBilling\Database\Seeders\ChargeableServiceHumanFriendlyNamesSeeder;
use Illuminate\Database\Migrations\Migration;

class SeedHumanFriendlyNamesForChargeableServices extends Migration
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
        if (isCpm()) {
            Artisan::call('db:seed', [
                '--class' => ChargeableServiceHumanFriendlyNamesSeeder::class,
            ]);
        }
    }
}
