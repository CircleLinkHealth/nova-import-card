<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests\Database\Repositories;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository as PatientServiceProcessorRepositoryInterface;
use CircleLinkHealth\CcmBilling\Entities\ChargeableLocationMonthlySummary;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use CircleLinkHealth\CcmBilling\Facades\BillingCache;
use CircleLinkHealth\CcmBilling\Repositories\LocationProcessorEloquentRepository;
use CircleLinkHealth\CcmBilling\Repositories\PatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientServiceProcessorOutputDTO;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Tests\CustomerTestCase;
use CircleLinkHealth\Customer\Traits\PracticeHelpers;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use CircleLinkHealth\SharedModels\DTO\ChargeableServiceDuration;
use CircleLinkHealth\SharedModels\Entities\PageTimer;

class PatientServiceRepositoryTest extends CustomerTestCase
{
    use PracticeHelpers;
    use UserHelpers;

    protected PatientServiceProcessorRepositoryInterface $repo;

    public function setUp(): void
    {
        parent::setUp();

        $this->repo = new PatientServiceProcessorRepository();
    }

    public function test_if_it_checks_if_service_is_available_for_location()
    {
        $ccmCode = ChargeableService::CCM;
        ChargeableLocationMonthlySummary::where('location_id', $locationId = $this->patient()->getPreferredContactLocation())->delete();
        (new LocationProcessorEloquentRepository())->store(
            $locationId,
            ChargeableService::getChargeableServiceIdUsingCode($ccmCode),
            $startOfMonth = Carbon::now()->startOfMonth()
        );

        self::assertTrue($this->repo->isChargeableServiceEnabledForLocationForMonth($this->patient()->id, $ccmCode, $startOfMonth));
        self::assertFalse($this->repo->isChargeableServiceEnabledForLocationForMonth($this->patient()->id, ChargeableService::BHI, $startOfMonth));
    }

    public function test_it_can_require_and_set_patient_consent()
    {
        $this->repo->store($patientId = $this->patient()->id, $ccmCode = ChargeableService::CCM, $startOfMonth = Carbon::now()->startOfMonth());
        self::assertFalse($this->repo->requiresPatientConsent($patientId, $ccmCode, $startOfMonth));

        $this->repo->store($patientId, $bhiCode = ChargeableService::BHI, $startOfMonth, true);
        self::assertTrue($this->repo->requiresPatientConsent($patientId, $bhiCode, $startOfMonth));

        $this->repo->setPatientConsented($patientId, $bhiCode, $startOfMonth);
        self::assertFalse($this->repo->requiresPatientConsent($patientId, $bhiCode, $startOfMonth));
    }

    public function test_it_checks_if_summary_is_attached()
    {
        $this->repo->store($patientId = $this->patient()->id, $ccmCode = ChargeableService::CCM, $startOfMonth = Carbon::now()->startOfMonth());

        self::assertTrue($this->repo->isAttached($patientId, $ccmCode, $startOfMonth));
        self::assertFalse($this->repo->isAttached($patientId, ChargeableService::BHI, $startOfMonth));
    }

    public function test_it_fulfills_summary()
    {
        $this->repo->store($patientId = $this->patient()->id, $ccmCode = ChargeableService::CCM, $startOfMonth = Carbon::now()->startOfMonth());

        self::assertTrue($this->repo->isAttached($patientId, $ccmCode, $startOfMonth));

        self::assertFalse($this->repo->isFulfilled($patientId, $ccmCode, $startOfMonth));

        $summary = $this->repo->fulfill($patientId, $ccmCode, $startOfMonth);

        self::assertTrue(is_a($summary, ChargeablePatientMonthlySummary::class));

        self::assertTrue($this->repo->isFulfilled($patientId, $ccmCode, $startOfMonth));
    }

    public function test_it_unfulfills_summary(){
        $this->repo->store($patientId = $this->patient()->id, $ccmCode = ChargeableService::CCM, $startOfMonth = Carbon::now()->startOfMonth());

        self::assertTrue($this->repo->isAttached($patientId, $ccmCode, $startOfMonth));

        self::assertFalse($this->repo->isFulfilled($patientId, $ccmCode, $startOfMonth));

        $summary = $this->repo->fulfill($patientId, $ccmCode, $startOfMonth);

        self::assertTrue(is_a($summary, ChargeablePatientMonthlySummary::class));

        self::assertTrue($this->repo->isFulfilled($patientId, $ccmCode, $startOfMonth));

        $this->repo->multiAttachServiceSummaries(collect([
            (new PatientServiceProcessorOutputDTO())
                ->setCode($ccmCode)
            ->setIsFulfilling(false)
            ->setChargeableServiceId(ChargeableService::getChargeableServiceIdUsingCode($ccmCode))
            ->setPatientUserId($patientId)
            ->setChargeableMonth($startOfMonth)
            ->setRequiresConsent(false)
        ]));

        self::assertFalse($this->repo->isFulfilled($patientId, $ccmCode, $startOfMonth));
    }

    public function test_it_creates_patient_chargeable_activity(){
        $patient = $this->patient();

        $pageTimer = PageTimer::create([
            'provider_id' => $this->careCoach()->id,
            'start_time' => Carbon::now(),
            'patient_id' => $patient->id,

        ]);

        $this->repo->createActivityForChargeableService(
            'placeholder',
            $pageTimer,
            new ChargeableServiceDuration(
                $ccmId = ChargeableService::getChargeableServiceIdUsingCode(ChargeableService::CCM),
                $duration = 120,
                false
            )
        );

        $patientTime = $this->repo->getChargeablePatientTimesView($patient->id, Carbon::now()->startOfMonth());

        self::assertEquals($patientTime->firstWhere('chargeable_service_id', $ccmId)->total_time, $duration);
    }
    public function test_it_fetches_patient_monthly_chargeable_time()
    {

    }
    public function test_it_attaches_and_detaches_forced_chargeable_service()
    {

    }

    public function test_it_checks_for_location_chargeable_service_availability()
    {

    }

    public function test_it_multi_attaches_services_in_single_transaction()
    {

    }
}
