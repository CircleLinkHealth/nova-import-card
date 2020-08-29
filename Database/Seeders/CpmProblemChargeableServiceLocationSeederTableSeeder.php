<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Database\Seeders;

use CircleLinkHealth\CcmBilling\Jobs\SeedPracticeCpmProblemChargeableServicesFromLegacyTables;
use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Database\Seeder;

class CpmProblemChargeableServiceLocationSeederTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Practice::get()->each(function (Practice $practice) {
            SeedPracticeCpmProblemChargeableServicesFromLegacyTables::dispatch($practice->id);
        });
    }
}
