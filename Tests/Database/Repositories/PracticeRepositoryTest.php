<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests\Database\Repositories;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Domain\Patient\LogPatientCcmStatusForEndOfMonth;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use CircleLinkHealth\CcmBilling\Repositories\PracticeProcessorEloquentRepository;
use Tests\CustomerTestCase;

class PracticeRepositoryTest extends CustomerTestCase
{
    protected PracticeProcessorEloquentRepository $repo;

    //todo: extend customer Test Case, add custom assertions as well
    protected function setUp(): void
    {
        parent::setUp();

        $this->repo = new PracticeProcessorEloquentRepository();
    }

    public function test_it_fetches_location_patient_services_for_month()
    {
        $patients     = $this->patient($numberOfPatients = 5);
        $startOfMonth = Carbon::now()->startOfMonth();
        self::assertTrue(is_array($patients));

        $practiceId = null;
        foreach ($patients as $patient) {
            $practiceId = $practiceId ?? $patient->getPrimaryPracticeId();
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

        self::assertTrue($this->repo->patientServices($practiceId, $startOfMonth)->count() === $numberOfPatients * 2);
    }

    public function test_it_fetches_location_patients_with_billing_relationships_loaded()
    {
        //todo: address duplicate code
        $patients = $this->repo->patients($practiceId = $this->patient(5)[0]->getPrimaryPracticeId(), $startOfMonth = Carbon::now()->startOfMonth());

        foreach ($patients as $patient) {
            self::assertTrue($patient->endOfMonthCcmStatusLogs->isEmpty());
            self::assertTrue($patient->chargeableMonthlySummaries->isEmpty());

            LogPatientCcmStatusForEndOfMonth::create($patient->id, $patient->getCcmStatus(), $startOfMonth);

            $patient->chargeableMonthlySummaries()->createMany(
                [
                    [
                        'chargeable_service_id' => 1,
                        'chargeable_month'      => $startOfMonth,
                    ],
                    [
                        'chargeable_service_id' => 2,
                        'chargeable_month'      => $startOfMonth,
                    ],
                ]
            );
        }

        $patients = $this->repo->patients($practiceId, $startOfMonth);

        foreach ($patients as $patient) {
            self::assertTrue(($logs = $patient->endOfMonthCcmStatusLogs)->isNotEmpty());
            self::assertTrue(1 === $logs->count());
            self::assertTrue(($summaries = $patient->chargeableMonthlySummaries)->isNotEmpty());
            self::assertTrue(2 == $summaries->count());
        }
    }
}
