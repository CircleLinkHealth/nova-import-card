<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\Nova\Actions\ModifyTimeTracker;
use CircleLinkHealth\CcmBilling\Facades\BillingCache;
use CircleLinkHealth\Customer\Traits\PracticeHelpers;
use CircleLinkHealth\Customer\Traits\TimeHelpers;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use CircleLinkHealth\SharedModels\Entities\PageTimer;
use JoshGaber\NovaUnit\Actions\NovaActionTest;
use Tests\CustomerTestCase;

class ModifyTimeTrackingTest extends CustomerTestCase
{
    use PracticeHelpers;
    use TimeHelpers;
    use UserHelpers;

    public function test_that_it_fails_to_modify_if_time_accrued_towards_and_flag_not_set()
    {
        $practice = $this->setupPractice(true, true);
        $nurse    = $this->getNurse($practice->id, true);
        $patient  = $this->setupPatient($practice);
        $this->addTime($nurse, $patient, 5, true, true, null);

        $time = $patient->getCcmTime();
        self::assertEquals(5 * 60, $time);

        $careRateLogs = $nurse->nurseInfo->careRateLogs()->sum('increment');
        self::assertEquals(5 * 60, $careRateLogs);

        /** @var PageTimer $entry */
        $entry  = PageTimer::orderByDesc('id')->first();
        $action = NovaActionTest::novaAction(ModifyTimeTracker::class);
        $action->handle([
            'duration'              => 3 * 60,
            'allow_accrued_towards' => false,
        ], $entry);

        $entry = $entry->fresh();
        self::assertEquals(5 * 60, $entry->duration);

        $activityDuration = $entry->activities()->sum('duration');
        self::assertEquals(5 * 60, $activityDuration);

        $time = $patient->getCcmTime();
        self::assertEquals(5 * 60, $time);

        $careRateLogs = $nurse->nurseInfo->fresh()->careRateLogs()->sum('increment');
        self::assertEquals(5 * 60, $careRateLogs);
    }

    public function test_that_it_modifies_billable_time_tracked()
    {
        $practice = $this->setupPractice(true, true);
        $nurse    = $this->getNurse($practice->id, true);
        $patient  = $this->setupPatient($practice);
        $this->addTime($nurse, $patient, 5, true, true, null);

        $time = $patient->getCcmTime();
        self::assertEquals(5 * 60, $time);

        $careRateLogs = $nurse->nurseInfo->careRateLogs()->sum('increment');
        self::assertEquals(5 * 60, $careRateLogs);

        /** @var PageTimer $entry */
        $entry  = PageTimer::orderByDesc('id')->first();
        $action = NovaActionTest::novaAction(ModifyTimeTracker::class);
        $action->handle([
            'duration'              => 3 * 60,
            'allow_accrued_towards' => true,
        ], $entry);

        BillingCache::clearPatients();

        $entry = $entry->fresh();
        self::assertEquals(3 * 60, $entry->duration);

        $activityDuration = $entry->activities()->sum('duration');
        self::assertEquals(3 * 60, $activityDuration);

        $time = $patient->getCcmTime();
        self::assertEquals(3 * 60, $time);

        $careRateLogs = $nurse->nurseInfo->fresh()->careRateLogs()->sum('increment');
        self::assertEquals(3 * 60, $careRateLogs);
    }

    public function test_that_it_modifies_non_billable_time_tracked()
    {
        $practice = $this->setupPractice(true, true);
        $nurse    = $this->getNurse($practice->id, true);
        $patient  = $this->setupPatient($practice);
        $this->addTime($nurse, $patient, 5, false, false, null);

        $time = $patient->getCcmTime();
        self::assertEquals(0, $time);

        $careRateLogs = $nurse->nurseInfo->careRateLogs()->sum('increment');
        self::assertEquals(0, $careRateLogs);

        /** @var PageTimer $entry */
        $entry  = PageTimer::orderByDesc('id')->first();
        $action = NovaActionTest::novaAction(ModifyTimeTracker::class);
        $action->handle([
            'duration' => 3 * 60,
        ], $entry);

        $entry = $entry->fresh();
        self::assertEquals(3 * 60, $entry->duration);

        $activityDuration = $entry->activities()->sum('duration');
        self::assertEquals(0, $activityDuration);

        $time = $patient->getCcmTime();
        self::assertEquals(0, $time);

        $careRateLogs = $nurse->nurseInfo->fresh()->careRateLogs()->sum('increment');
        self::assertEquals(0, $careRateLogs);
    }

    private function getNurse(int $practiceId, $enableCcmPlus)
    {
        $nurse = $this->createUser($practiceId, 'care-center');

        return $this->setupNurse($nurse, true, 20.0, $enableCcmPlus, 12.50);
    }
}
