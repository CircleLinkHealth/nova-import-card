<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Traits;

use CircleLinkHealth\CcmBilling\Domain\Customer\SetupPracticeBillingData;
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
        bool $addPcmService = false,
        bool $addRpmService = false,
        bool $addRhcService = false
    ) {
        if ( ! Location::where('practice_id', $practice->id)->exists()) {
            factory(Location::class)->create(['practice_id' => $practice->id]);
        }

        $sync = [];

        $cached = ChargeableService::cached();

        if ($addCcmService) {
            $ccmService            = $cached->where('code', '=', ChargeableService::CCM)->first();
            $sync[$ccmService->id] = ['amount' => 29.0];
        }

        if ($addCcmPlusServices) {
            $ccmPlus40            = $cached->where('code', '=', ChargeableService::CCM_PLUS_40)->first();
            $ccmPlus60            = $cached->where('code', '=', ChargeableService::CCM_PLUS_60)->first();
            $sync[$ccmPlus40->id] = ['amount' => 28.0];
            $sync[$ccmPlus60->id] = ['amount' => 28.0];
        }

        if ($addBhiService) {
            $bhi            = $cached->where('code', '=', ChargeableService::BHI)->first();
            $sync[$bhi->id] = ['amount' => 28.0];
        }

        if ($addPcmService) {
            $bhi            = $cached->where('code', '=', ChargeableService::PCM)->first();
            $sync[$bhi->id] = ['amount' => 27.0];
        }

        if ($addRpmService) {
            $rpm              = $cached->where('code', '=', ChargeableService::RPM)->first();
            $rpm40            = $cached->where('code', '=', ChargeableService::RPM40)->first();
            $sync[$rpm->id]   = ['amount' => 29.0];
            $sync[$rpm40->id] = ['amount' => 29.0];
        }

        if ($addRhcService) {
            $rhc            = $cached->where('code', '=', ChargeableService::GENERAL_CARE_MANAGEMENT)->first();
            $sync[$rhc->id] = ['amount' => 25.0];
        }

        $practice->chargeableServices()->sync($sync);

        SetupPracticeBillingData::sync($practice->id);

        return $practice;
    }

    private function setupPractice(
        bool $addCcmService = false,
        bool $addCcmPlusServices = false,
        bool $addBhiService = false,
        bool $addPcmService = false,
        bool $addRpmService = false
    ): Practice {
        $practice = factory(Practice::class)->create();

        return $this->setupExistingPractice(
            $practice,
            $addCcmService,
            $addCcmPlusServices,
            $addBhiService,
            $addPcmService,
            $addRpmService
        );
    }
}
