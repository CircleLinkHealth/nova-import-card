<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests\Database\Repositories;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use CircleLinkHealth\CcmBilling\Processors\Patient\BHI;
use CircleLinkHealth\CcmBilling\Processors\Patient\CCM;
use CircleLinkHealth\CcmBilling\Processors\Patient\PCM;
use CircleLinkHealth\CcmBilling\Repositories\LocationProcessorEloquentRepository;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Location;
use Tests\CustomerTestCase;

class LocationRepositoryTest extends CustomerTestCase
{
    protected Location $location;
    protected LocationProcessorEloquentRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->location = factory(Location::class)->create();
        $this->repo     = new LocationProcessorEloquentRepository();
    }

    public function test_it_fetches_available_location_processors_for_month()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        foreach ([
            ChargeableService::CCM,
            ChargeableService::BHI,
            ChargeableService::PCM,
        ] as  $code) {
            $this->repo->store($this->location->id, $code, $startOfMonth);
        }

        $processors = $this->repo->availableLocationServiceProcessors($this->location->id, $startOfMonth);

        self::assertNotNull($processors->getCcm());
        self::assertTrue(is_a($processors->getCcm(), CCM::class));
        self::assertNotNull($processors->getBhi());
        self::assertTrue(is_a($processors->getBhi(), BHI::class));
        self::assertNotNull($processors->getPcm());
        self::assertTrue(is_a($processors->getPcm(), PCM::class));

        self::assertNull($processors->getCcm40());
        self::assertNull($processors->getCcm60());
        self::assertNull($processors->getAwv1());
        self::assertNull($processors->getAwv2());
    }

    public function test_it_fetches_location_patient_services_for_month()
    {
        $patients     = $this->patient($numberOfPatients = 5);
        $startOfMonth = Carbon::now()->startOfMonth();
        self::assertTrue(is_array($patients));

        $locationId = null;
        foreach ($patients as $patient) {
            $locationId = $locationId ?? $patient->getPreferredContactLocation();
            ChargeablePatientMonthlySummary::insert([
                [
                    'patient_user_id'       => $patient->id,
                    'chargeable_service_id' => 1,
                    'chargeable_month'      => $startOfMonth,
                ],
                [
                    'patient_user_id'       => $patient->id,
                    'chargeable_service_id' => 2,
                    'chargeable_month'      => $startOfMonth,
                ],
            ]);
        }

        self::assertTrue($this->repo->patientServices($locationId, $startOfMonth)->count() === $numberOfPatients * 2);
    }

    public function test_it_fetches_location_patients_with_billing_relationships_loaded()
    {
        //create location patients with relevant data
        //fetch them and assert you got what you gave, dave
    }
}
