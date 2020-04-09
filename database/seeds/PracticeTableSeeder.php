<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\SaasAccount;
use Illuminate\Database\Seeder;

class PracticeTableSeeder extends Seeder
{
    public function run()
    {
        \DB::table('practices')->delete();

        $saasAccount = SaasAccount::firstOrFail();

        factory(Practice::class, 5)->create(['active' => true, 'saas_account_id' => $saasAccount->id])->each(function (Practice $practice) {
            factory(Location::class)->create(['practice_id' => $practice->id, 'is_primary' => true]);
            $practice->chargeableServices()->sync(ChargeableService::whereIn('code', ChargeableService::DEFAULT_CHARGEABLE_SERVICE_CODES)->pluck('id'));
        });
    }
}
