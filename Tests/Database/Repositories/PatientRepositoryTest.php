<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests\Database\Repositories;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Domain\Patient\LogPatientCcmStatusForEndOfMonth;
use CircleLinkHealth\CcmBilling\Repositories\LocationProcessorEloquentRepository;
use CircleLinkHealth\CcmBilling\Repositories\PatientProcessorEloquentRepository;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use Tests\CustomerTestCase;

class PatientRepositoryTest extends CustomerTestCase
{
    protected PatientProcessorEloquentRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repo = new PatientProcessorEloquentRepository();
    }

    public function test_it_fetches_patient_with_billing_data_including_location_for_month()
    {
        $locationId = $this->patient()->getPreferredContactLocation();

        foreach ([
            ChargeableService::CCM,
            ChargeableService::BHI,
            ChargeableService::PCM,
        ] as  $code) {
            (new LocationProcessorEloquentRepository())->store($locationId, $code, $startOfMonth = Carbon::now()->startOfMonth());
        }

        LogPatientCcmStatusForEndOfMonth::create($this->patient()->id, $this->patient()->getCcmStatus(), $startOfMonth);

        $this->patient()->chargeableMonthlySummaries()->createMany(
            [
                [
                    'chargeable_service_id' => ChargeableService::getChargeableServiceIdUsingCode(ChargeableService::BHI),
                    'chargeable_month'      => $startOfMonth,
                ],
                [
                    'chargeable_service_id' => ChargeableService::getChargeableServiceIdUsingCode(ChargeableService::CCM),
                    'chargeable_month'      => $startOfMonth,
                ],
            ]
        );

        $patient = $this->repo->patientWithBillingDataForMonth($this->patient()->id, $startOfMonth)->first();

        self::assertNotNull($info = $patient->patientInfo);
        self::assertNotNull($location = $info->location);
        self::assertTrue(($locationServices = $location->chargeableServiceSummaries)->isNotEmpty());
        self::assertTrue(
            1 === $locationServices
                ->where('chargeable_service_id', ChargeableService::getChargeableServiceIdUsingCode(ChargeableService::BHI))
                ->where('chargeable_month', $startOfMonth)
                ->count()
        );
        self::assertTrue(
            1 === $locationServices
                ->where('chargeable_service_id', ChargeableService::getChargeableServiceIdUsingCode(ChargeableService::CCM))
                ->where('chargeable_month', $startOfMonth)
                ->count()
        );
        self::assertTrue(
            1 === $locationServices
                ->where('chargeable_service_id', ChargeableService::getChargeableServiceIdUsingCode(ChargeableService::PCM))
                ->where('chargeable_month', $startOfMonth)
                ->count()
        );
        self::assertTrue(
            0 === $locationServices
                ->where('chargeable_service_id', ChargeableService::getChargeableServiceIdUsingCode(ChargeableService::AWV_INITIAL))
                ->where('chargeable_month', $startOfMonth)
                ->count()
        );
        self::assertTrue(
            0 === $locationServices
                ->where('chargeable_service_id', ChargeableService::getChargeableServiceIdUsingCode(ChargeableService::AWV_SUBSEQUENT))
                ->where('chargeable_month', $startOfMonth)
                ->count()
        );
        self::assertTrue(
            0 === $locationServices
                ->where('chargeable_service_id', ChargeableService::getChargeableServiceIdUsingCode(ChargeableService::GENERAL_CARE_MANAGEMENT))
                ->where('chargeable_month', $startOfMonth)
                ->count()
        );
        self::assertTrue(($logs = $patient->endOfMonthCcmStatusLogs)->isNotEmpty());
        self::assertTrue(1 === $logs->count());
        self::assertTrue(($summaries = $patient->chargeableMonthlySummaries)->isNotEmpty());
        self::assertTrue(2 == $summaries->count());
    }
}
