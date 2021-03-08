<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests\Database;

use CircleLinkHealth\CcmBilling\Domain\Patient\PatientProblemsForBillingProcessing;
use CircleLinkHealth\CcmBilling\Repositories\LocationProblemServiceRepository;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientProblemForProcessing;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Tests\CustomerTestCase;
use CircleLinkHealth\ApiPatient\ValueObjects\CcdProblemInput;
use CircleLinkHealth\SharedModels\Entities\CpmProblem;
use CircleLinkHealth\SharedModels\Services\CCD\CcdProblemService;

class LocationProblemServiceDatabaseTest extends CustomerTestCase
{
    protected LocationProblemServiceRepository $repo;

    public function setUp(): void
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

        (app(CcdProblemService::class))->addPatientCcdProblem(
            (new CcdProblemInput())
                ->setCpmProblemId($cpmProblem->id)
                ->setUserId($this->patient()->id)
                ->setName($cpmProblem->name)
                ->setIsMonitored(true)
        );

        self::assertTrue(
            is_a(
                $patientProblemForProcessing = PatientProblemsForBillingProcessing::getCollection($this->patient()->id)->first(),
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
