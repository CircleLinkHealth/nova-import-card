<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\Nova\Actions\ModifyPatientTime;
use CircleLinkHealth\CcmBilling\Facades\BillingCache;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\NurseCareRateLog;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Traits\PracticeHelpers;
use CircleLinkHealth\Customer\Traits\TimeHelpers;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use CircleLinkHealth\SharedModels\Entities\Activity;
use CircleLinkHealth\SharedModels\Entities\PageTimer;
use JoshGaber\NovaUnit\Actions\NovaActionTest;
use CircleLinkHealth\Core\Tests\TestCase;

class ModifyPatientTimeTest extends TestCase
{
    use PracticeHelpers;
    use TimeHelpers;
    use UserHelpers;

    private int $ccmChargeableServiceId;

    public function setUp(): void
    {
        parent::setUp();
        $this->ccmChargeableServiceId = ChargeableService::firstWhere('code', '=', ChargeableService::CCM)->id;
    }

    public function test_it_modifies_45_minutes_to_25_minutes()
    {
        $practice = $this->setupPractice(true, true);
        $nurse    = $this->getNurse($practice->id, true);
        $patient  = $this->setupPatient($practice);
        $this->addTime($nurse, $patient, 2, true, true, $this->ccmChargeableServiceId);
        $this->addTime($nurse, $patient, 3, true, true, $this->ccmChargeableServiceId);
        $this->addTime($nurse, $patient, 40, true, true, $this->ccmChargeableServiceId);

        $this->validateTime($patient, $nurse, 20 * 60, 25 * 60);

        $action = NovaActionTest::novaAction(ModifyPatientTime::class);
        $action->handle([
            'chargeable_service'    => ChargeableService::CCM,
            'durationMinutes'       => 25,
            'allow_accrued_towards' => true,
        ], $patient);

        BillingCache::clearPatients();

        $this->validateTime($patient, $nurse, 20 * 60, 5 * 60);
    }

    public function test_it_modifies_65_minutes_to_25_minutes()
    {
        $practice = $this->setupPractice(true, true);
        $nurse    = $this->getNurse($practice->id, true);
        $patient  = $this->setupPatient($practice);
        $this->addTime($nurse, $patient, 2, true, true, $this->ccmChargeableServiceId);
        $this->addTime($nurse, $patient, 3, true, true, $this->ccmChargeableServiceId);
        $this->addTime($nurse, $patient, 16, true, true, $this->ccmChargeableServiceId);
        $this->addTime($nurse, $patient, 21, true, true, $this->ccmChargeableServiceId);
        $this->addTime($nurse, $patient, 23, true, true, $this->ccmChargeableServiceId);

        $this->validateTime($patient, $nurse, 20 * 60, 45 * 60);

        $action = NovaActionTest::novaAction(ModifyPatientTime::class);
        $action->handle([
            'chargeable_service'    => ChargeableService::CCM,
            'durationMinutes'       => 25,
            'allow_accrued_towards' => true,
        ], $patient);

        BillingCache::clearPatients();

        $this->validateTime($patient, $nurse, 20 * 60, 5 * 60);
    }

    public function test_it_modifies_time_from_multiple_page_timers()
    {
        $practice = $this->setupPractice(true, true);
        $nurse    = $this->getNurse($practice->id, true);
        $patient  = $this->setupPatient($practice);
        $this->addTime($nurse, $patient, 2, true, true, $this->ccmChargeableServiceId);
        $this->addTime($nurse, $patient, 3, true, true, $this->ccmChargeableServiceId);

        $time = $patient->getCcmTime();
        self::assertEquals(5 * 60, $time);

        $this->validateTime($patient, $nurse, 5 * 60, 0 * 60);

        $action = NovaActionTest::novaAction(ModifyPatientTime::class);
        $action->handle([
            'chargeable_service'    => ChargeableService::CCM,
            'durationMinutes'       => 1,
            'allow_accrued_towards' => true,
        ], $patient);

        BillingCache::clearPatients();

        $this->validateTime($patient, $nurse, 1 * 60, 0 * 60);
    }

    public function test_it_modifies_time_from_page_timer_going_above_and_below_20_minute_range()
    {
        $practice = $this->setupPractice(true, true);
        $nurse    = $this->getNurse($practice->id, true);
        $patient  = $this->setupPatient($practice);
        $this->addTime($nurse, $patient, 2, true, true, $this->ccmChargeableServiceId);
        $this->addTime($nurse, $patient, 3, true, true, $this->ccmChargeableServiceId);
        $this->addTime($nurse, $patient, 16, true, true, $this->ccmChargeableServiceId);

        $this->validateTime($patient, $nurse, 20 * 60, 1 * 60);

        $action = NovaActionTest::novaAction(ModifyPatientTime::class);
        $action->handle([
            'chargeable_service'    => ChargeableService::CCM,
            'durationMinutes'       => 19,
            'allow_accrued_towards' => true,
        ], $patient);

        BillingCache::clearPatients();

        $this->validateTime($patient, $nurse, 19 * 60, 0);
    }

    public function test_it_modifies_time_from_single_page_timer()
    {
        $practice = $this->setupPractice(true, true);
        $nurse    = $this->getNurse($practice->id, true);
        $patient  = $this->setupPatient($practice);
        $this->addTime($nurse, $patient, 2, true, true, $this->ccmChargeableServiceId);
        $this->addTime($nurse, $patient, 3, true, true, $this->ccmChargeableServiceId);

        $this->validateTime($patient, $nurse, 5 * 60, 0);

        $action = NovaActionTest::novaAction(ModifyPatientTime::class);
        $action->handle([
            'chargeable_service'    => ChargeableService::CCM,
            'durationMinutes'       => 4,
            'allow_accrued_towards' => true,
        ], $patient);

        BillingCache::clearPatients();

        $this->validateTime($patient, $nurse, 4 * 60, 0);
    }

    private function getNurse(int $practiceId, $enableCcmPlus)
    {
        $nurse = $this->createUser($practiceId, 'care-center');

        return $this->setupNurse($nurse, true, 20.0, $enableCcmPlus, 12.50);
    }

    private function validateTime(User $patient, User $nurse, int $timeTowardsCcm, int $timeAboveCcm)
    {
        $totalTime         = $timeTowardsCcm + $timeAboveCcm;
        $pageTimerDuration = PageTimer::wherePatientId($patient->id)->sum('duration');
        self::assertEquals($totalTime, $pageTimerDuration);

        $activityDuration = Activity::wherePatientId($patient->id)->sum('duration');
        self::assertEquals($totalTime, $activityDuration);

        $time = $patient->getCcmTime();
        self::assertEquals($totalTime, $time);

        $careRateLogs = $nurse->nurseInfo->careRateLogs()->sum('increment');
        self::assertEquals($totalTime, $careRateLogs);

        $careRateLogs = $nurse->nurseInfo->careRateLogs()->where('ccm_type', '=', 'accrued_towards_ccm')->sum('increment');
        self::assertEquals($timeTowardsCcm, $careRateLogs);

        $careRateLogs = $nurse->nurseInfo->careRateLogs()->where('ccm_type', '=', 'accrued_after_ccm')->sum('increment');
        self::assertEquals($timeAboveCcm, $careRateLogs);

        /** @var NurseCareRateLog $nurseCareRateLog */
        $nurseCareRateLog = $nurse->nurseInfo->careRateLogs()->orderByDesc('time_before')->first();
        self::assertEquals($totalTime, $nurseCareRateLog->time_before + $nurseCareRateLog->increment);
    }
}
