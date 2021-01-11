<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Database\Seeders;

use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class SeedPatientDataForBillingTestTableSeeder extends Seeder
{
    use UserHelpers;

    protected $location;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $practice = Practice::updateOrCreate([
            'name' => 'billing-practice',
        ], [
            'display_name'             => $name = 'Billing Practice',
            'active'                   => true,
            'federal_tax_id'           => generateRandomIntegerOfDigitSize(5),
            'clh_pppm'                 => 0,
            'weekly_report_recipients' => 'mantoniou@circlelinkhealth.com',
            'invoice_recipients'       => 'mantoniou@circlelinkhealth.com',
            'bill_to_name'             => $name,
            'send_alerts'              => 1,
            'outgoing_phone_number'    => '+'.generateRandomIntegerOfDigitSize(10),
            'term_days'                => 30,
        ]);

        $practice->locations()->createMany([
            [
                'name'       => 'Billing Location 1',
                'is_primary' => 1,
            ],
            [
                'name'       => 'Billing Location 2',
                'is_primary' => 0,
            ],
            [
                'name'       => 'Billing Location 3',
                'is_primary' => 0,
            ],
        ]);

        $practice->chargeableServices()->sync([
            ChargeableService::getChargeableServiceIdUsingCode(ChargeableService::BHI),
            ChargeableService::getChargeableServiceIdUsingCode(ChargeableService::CCM),
            ChargeableService::getChargeableServiceIdUsingCode(ChargeableService::PCM),
        ]);

        $this->location = $practice->locations()->get()->first();
        for ($i = 20; $i > 0; --$i) {
            $this->setupPatient($practice, rand(0, 1));
        }
    }
}
