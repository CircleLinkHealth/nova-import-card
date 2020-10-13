<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\Nova\Actions\ModifyTimeTracker;
use App\Traits\Tests\PracticeHelpers;
use App\Traits\Tests\TimeHelpers;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use CircleLinkHealth\TimeTracking\Entities\PageTimer;
use JoshGaber\NovaUnit\Actions\NovaActionTest;
use Tests\CustomerTestCase;

class ModifyTimeTrackingTest extends CustomerTestCase
{
    use PracticeHelpers;
    use TimeHelpers;
    use UserHelpers;

    public function test_that_it_fails_to_modify_if_time_accrued_towards_and_flag_not_set()
    {
        $nurse   = $this->careCoach();
        $patient = $this->patient();
        $this->addTime($nurse, $patient, 5, true, true, false);

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
        $nurse   = $this->careCoach();
        $patient = $this->patient();
        $this->addTime($nurse, $patient, 5, true, true, false);

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

        $entry = $entry->fresh();
        self::assertEquals(3 * 60, $entry->duration);

        $activityDuration = $entry->activities()->sum('duration');
        self::assertEquals(3 * 60, $activityDuration);

        $time = $patient->getCcmTime();
        self::assertEquals(3 * 60, $time);

        $careRateLogs = $nurse->nurseInfo->fresh()->careRateLogs()->sum('increment');
        self::assertEquals(3 * 60, $careRateLogs);
    }

    public function test_that_it_modifies_billable_time_tracked_that_spans_into_three_activities()
    {
        $practice = $this->setupPractice(true, true, false, false);
        $nurse    = $this->getNurse($practice->id, true);
        $patient  = $this->setupPatient($practice, false, false);
        $this->addTime($nurse, $patient, 45, true, true, false);

        $time = $patient->getCcmTime();
        self::assertEquals(45 * 60, $time);

        $careRateLogs = $nurse->nurseInfo->careRateLogs()->sum('increment');
        self::assertEquals(45 * 60, $careRateLogs);

        /** @var PageTimer $entry */
        $entry  = PageTimer::orderByDesc('id')->first();
        $action = NovaActionTest::novaAction(ModifyTimeTracker::class);
        $action->handle([
            'duration'              => 25 * 60,
            'allow_accrued_towards' => false,
        ], $entry);

        $entry = $entry->fresh();
        self::assertEquals(25 * 60, $entry->duration);

        $activityDuration = $entry->activities()->sum('duration');
        self::assertEquals(25 * 60, $activityDuration);

        $time = $patient->getCcmTime();
        self::assertEquals(25 * 60, $time);

        $careRateLogs = $nurse->nurseInfo->fresh()->careRateLogs()->sum('increment');
        self::assertEquals(25 * 60, $careRateLogs);
    }

    public function test_that_it_modifies_billable_time_tracked_that_spans_into_three_activities_2()
    {
        $practice = $this->setupPractice(true, true, false, false);
        $nurse    = $this->getNurse($practice->id, true);
        $patient  = $this->setupPatient($practice, false, false);
        $this->addTime($nurse, $patient, 45, true, true, false);

        $time = $patient->getCcmTime();
        self::assertEquals(45 * 60, $time);

        $careRateLogs = $nurse->nurseInfo->careRateLogs()->sum('increment');
        self::assertEquals(45 * 60, $careRateLogs);

        /** @var PageTimer $entry */
        $entry  = PageTimer::orderByDesc('id')->first();
        $action = NovaActionTest::novaAction(ModifyTimeTracker::class);
        $action->handle([
            'duration'              => 20 * 60,
            'allow_accrued_towards' => true,
        ], $entry);

        $entry = $entry->fresh();
        self::assertEquals(20 * 60, $entry->duration);

        $activityDuration = $entry->activities()->sum('duration');
        self::assertEquals(20 * 60, $activityDuration);

        $time = $patient->getCcmTime();
        self::assertEquals(20 * 60, $time);

        $careRateLogs = $nurse->nurseInfo->fresh()->careRateLogs()->sum('increment');
        self::assertEquals(20 * 60, $careRateLogs);
    }

    public function test_that_it_modifies_non_billable_time_tracked()
    {
        $nurse   = $this->careCoach();
        $patient = $this->patient();
        $this->addTime($nurse, $patient, 5, false, false, false);

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
