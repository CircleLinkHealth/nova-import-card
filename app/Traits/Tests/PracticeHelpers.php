<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits\Tests;

use CircleLinkHealth\CcmBilling\Jobs\GenerateLocationSummaries;
use CircleLinkHealth\CcmBilling\Jobs\GenerateServiceSummariesForAllPracticeLocations;
use CircleLinkHealth\CcmBilling\Jobs\MigrateChargeableServicesFromChargeablesToLocationSummariesTable;
use CircleLinkHealth\CcmBilling\Jobs\SeedPracticeCpmProblemChargeableServicesFromLegacyTables;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Practice;

trait PracticeHelpers
{
    private function setupExistingPractice(
        Practice $practice,
        bool $addCcmService = false,
        bool $addCcmPlusServices = false,
        bool $addBhiService = false,
        bool $addPcmService = false
    ) {
        $this->location = Location::where('practice_id', $practice->id)->first();

        if ( ! $this->location) {
            $this->location = factory(Location::class)->create(['practice_id' => $practice->id]);
        }

        $sync = [];

        if ($addCcmService) {
            $ccmService            = ChargeableService::where('code', '=', ChargeableService::CCM)->first();
            $sync[$ccmService->id] = ['amount' => 29.0];
        }

        if ($addCcmPlusServices) {
            $ccmPlus40            = ChargeableService::where('code', '=', ChargeableService::CCM_PLUS_40)->first();
            $ccmPlus60            = ChargeableService::where('code', '=', ChargeableService::CCM_PLUS_60)->first();
            $sync[$ccmPlus40->id] = ['amount' => 28.0];
            $sync[$ccmPlus60->id] = ['amount' => 28.0];
        }

        if ($addBhiService) {
            $bhi            = ChargeableService::where('code', '=', ChargeableService::BHI)->first();
            $sync[$bhi->id] = ['amount' => 28.0];
        }

        if ($addPcmService) {
            $bhi            = ChargeableService::where('code', '=', ChargeableService::PCM)->first();
            $sync[$bhi->id] = ['amount' => 27.0];
        }

        $practice->chargeableServices()->sync($sync);
    
        MigrateChargeableServicesFromChargeablesToLocationSummariesTable::dispatch();
        GenerateServiceSummariesForAllPracticeLocations::dispatch();
        SeedPracticeCpmProblemChargeableServicesFromLegacyTables::dispatch($practice->id);

        return $practice;
    }

    private function setupPractice(
        bool $addCcmService = false,
        bool $addCcmPlusServices = false,
        bool $addBhiService = false,
        bool $addPcmService = false
    ): Practice {
        $practice = factory(Practice::class)->create();

        return $this->setupExistingPractice(
            $practice,
            $addCcmService,
            $addCcmPlusServices,
            $addBhiService,
            $addPcmService
        );
    }
}
