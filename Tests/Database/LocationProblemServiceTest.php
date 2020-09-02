<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests\Database;

use CircleLinkHealth\CcmBilling\Repositories\LocationProblemServiceRepository;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientProblemForProcessing;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\SharedModels\Entities\CpmProblem;
use Tests\CustomerTestCase;

class LocationProblemServiceTest extends CustomerTestCase
{
    protected LocationProblemServiceRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repo = new LocationProblemServiceRepository();
    }

    public function test_locations_can_have_chargeable_services_attached_to_cpm_problems()
    {
        $cpmProblem = CpmProblem::whereIsBehavioral(true)->first();

        self::assertNotNull(
            $locationProblemService = $this->repo->store(
                $locationId = $this->location()->id,
                $cpmProblem->id,
                $bhiCodeId = ChargeableService::getChargeableServiceIdUsingCode($bhiCode = ChargeableService::BHI)
            )
        );

        self::assertTrue(
            in_array(
                $bhiCode,
                $cpmProblem->getChargeableServiceCodesForLocation($locationId)
            )
        );
    }

    public function test_patient_has_problems_with_chargeable_services_attached()
    {
        $cpmProblem = CpmProblem::whereIsBehavioral(true)->first();

        self::assertNotNull(
            $locationProblemService = $this->repo->store(
                $locationId = $this->patient()->getPreferredContactLocation(),
                $cpmProblem->id,
                $bhiCodeId = ChargeableService::getChargeableServiceIdUsingCode($bhiCode = ChargeableService::BHI)
            )
        );

        $this->patient()->ccdProblems()->create([
            'cpm_problem_id' => $cpmProblem->id,
            'name'           => $cpmProblem->name,
            'is_monitored'   => true,
        ]);

        self::assertTrue(
            is_a(
                $patientProblemForProcessing = $this->patient()->patientProblemsForBillingProcessing()->first(),
                PatientProblemForProcessing::class
            )
        );

        self::assertTrue(
            in_array(
                $bhiCode,
                $patientProblemForProcessing->getServiceCodes()
            )
        );
    }
}
