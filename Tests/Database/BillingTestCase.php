<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests\Database;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Jobs\GenerateLocationSummaries;
use CircleLinkHealth\CcmBilling\Jobs\MigrateChargeableServicesFromChargeablesToLocationSummariesTable;
use CircleLinkHealth\CcmBilling\Jobs\SeedPracticeCpmProblemChargeableServicesFromLegacyTables;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\AppConfig\PracticesRequiringSpecialBhiConsent;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Practice;
use Tests\CustomerTestCase;

class BillingTestCase extends CustomerTestCase
{
    protected Location $location;
    protected Practice $practice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setupPractice()
            ->setupLocation();
    }

    public function getLocation(): Location
    {
        if ( ! isset($this->location)) {
            $this->location = $this->patient()->location;
        }

        return $this->location;
    }

    public function getPractice(): Practice
    {
        if ( ! isset($this->practice)) {
            if ( ! isset($this->location)) {
                $this->location = $this->patient()->patientInfo->location;
            }
            $this->practice = $this->location->practice;
        }

        return $this->practice;
    }

    private function setupLocation(): self
    {
        MigrateChargeableServicesFromChargeablesToLocationSummariesTable::dispatch();
        GenerateLocationSummaries::dispatch(($location = $this->getLocation())->id, $startOfMOnth = Carbon::now()->startOfMonth());

        self::assertTrue($location->chargeableServiceSummaries()->with(['chargeableService'])->get()->isNotEmpty());
        self::assertTrue( ! is_null($location->chargeableServiceSummaries->firstWhere('chargeableService.code', ChargeableService::BHI)));
        self::assertTrue( ! is_null($location->chargeableServiceSummaries->firstWhere('chargeableService.code', ChargeableService::CCM)));
        self::assertTrue( ! is_null($location->chargeableServiceSummaries->firstWhere('chargeableService.code', ChargeableService::PCM)));

        return $this;
    }

    private function setupPractice(): self
    {
        ($practice = $this->getPractice())->chargeableServices()
            ->sync(
                [
                    ChargeableService::getChargeableServiceIdUsingCode($bhiCode = ChargeableService::BHI),
                    ChargeableService::getChargeableServiceIdUsingCode($bhiCode = ChargeableService::CCM),
                    ChargeableService::getChargeableServiceIdUsingCode($bhiCode = ChargeableService::PCM),
                ]
            );

        SeedPracticeCpmProblemChargeableServicesFromLegacyTables::dispatch($practice->id);

        AppConfig::updateOrCreate([
            'config_key'   => PracticesRequiringSpecialBhiConsent::PRACTICE_REQUIRES_SPECIAL_BHI_CONSENT_NOVA_KEY,
            'config_value' => $practice->name,
        ]);

        return $this;
    }
}
