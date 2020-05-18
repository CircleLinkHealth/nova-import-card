<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Services\Enrollment\EnrollableCallQueue;
use App\Traits\Tests\CareAmbassadorHelpers;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Tests\TestCase;

class CareAmbassadorQueueTest extends TestCase
{
    use \App\Traits\Tests\UserHelpers;
    use CareAmbassadorHelpers;

    protected $careAmbassadorUser;
    protected $enrollee;
    protected $practice;
    protected $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->practice           = factory(Practice::class)->create();
        $this->careAmbassadorUser = $this->createUser($this->practice->id, 'care-ambassador');
        $this->provider           = $this->createUser($this->practice->id, 'provider');
        $this->enrollee           = factory(Enrollee::class)->create();
    }

    /**
     * Get all careAmbassador enrollees / do not prioritize speaks spanish.
     *
     * TOP - 1st PRIO - patients in qache (confirmed family members)
     * 2nd prio - Confirmed family members whom statuses have not been confirmed - edge case
     * 3rd prio - Patients who requested call today (or in the past days and they havent been called)
     * 4th prio - call queue, patients that haven't been called yet
     * 5th prio - utc patients where attempt count 1 && last attempt > 3 days ago
     * 6th prio - >> attempt count 2.
     *
     * Post conditions - never bring enrolled, consented, soft or hard decline, utc x3, ineligible, legacy
     * if patient is spanish and CA does not speak spanish, re-assign.
     */
    public function test_queue_priority()
    {
        auth()->login($this->careAmbassadorUser);

        //add some enrollees that should never come in CAs queue -> declined, legacy, enrolled, ineligible, utc x3
        $this->createNeverToBeViewedEnrollees();

        //CREATE ENROLLEES AND ASSIGN TO CARE AMBASSADOR

        //setup enrollees for practice,
        //assign to CA and set status to match each of the priorities and post conditions above

        //Lets create 2 enrollees for 1st prio - these 2 will be confirmed family members of $this->enrollee.
        //So the will take priority in CAs queue. The first will come, then the other
        $enrollee1stPrio1 = factory(Enrollee::class)->create([
            'practice_id'             => $this->practice->id,
            'care_ambassador_user_id' => $this->careAmbassadorUser->id,
            'status'                  => Enrollee::TO_CONFIRM_UNREACHABLE,
            //attempt count irrelevant
            'attempt_count' => 0,
        ]);
        $enrollee1stPrio2 = factory(Enrollee::class)->create([
            'practice_id'             => $this->practice->id,
            'care_ambassador_user_id' => $this->careAmbassadorUser->id,
            'status'                  => Enrollee::TO_CONFIRM_UNREACHABLE,
            //attempt count irrelevant
            'attempt_count' => 0,
        ]);

        $confirmedFamilyMembers = implode(',', [
            $enrollee1stPrio1->id,
            $enrollee1stPrio2->id,
        ]);

        //add confirmed family members to cache
        EnrollableCallQueue::update($this->careAmbassadorUser->careAmbassador, $this->enrollee, $confirmedFamilyMembers);

        $enrollee2ndPrio = factory(Enrollee::class)->create([
            'practice_id'             => $this->practice->id,
            'care_ambassador_user_id' => $this->careAmbassadorUser->id,
            'status'                  => Enrollee::TO_CONFIRM_UNREACHABLE,
            //attempt count irrelevant
            'attempt_count' => 0,
        ]);

        $enrollee3rdPrio = factory(Enrollee::class)->create([
            'practice_id'             => $this->practice->id,
            'care_ambassador_user_id' => $this->careAmbassadorUser->id,
            'status'                  => Enrollee::UNREACHABLE,
            'attempt_count'           => 1,
            'requested_callback'      => Carbon::now()->toDateString(),
        ]);

        $enrollee4thPrio = factory(Enrollee::class)->create([
            'practice_id'             => $this->practice->id,
            'care_ambassador_user_id' => $this->careAmbassadorUser->id,
            'status'                  => Enrollee::TO_CALL,
            'attempt_count'           => 0,
        ]);

        $enrollee5thPrio = factory(Enrollee::class)->create([
            'practice_id'             => $this->practice->id,
            'care_ambassador_user_id' => $this->careAmbassadorUser->id,
            'status'                  => Enrollee::UNREACHABLE,
            'attempt_count'           => 1,
            'last_attempt_at'         => Carbon::now()->subDay(5),
        ]);

        $enrollee6thPrio = factory(Enrollee::class)->create([
            'practice_id'             => $this->practice->id,
            'care_ambassador_user_id' => $this->careAmbassadorUser->id,
            'status'                  => Enrollee::UNREACHABLE,
            'attempt_count'           => 2,
            'last_attempt_at'         => Carbon::now()->subDay(5),
        ]);

        //GET ENROLLEES FROM CALL QUEUE:
        //ASSERT CORRECT ORDER
        //ASSERT THAT NO INVALID ENROLLEES WILL COME

        //1st Prio - 1st confirmed family member
        $next = EnrollableCallQueue::getNext($this->careAmbassadorUser->careAmbassador);

        $this->assertNotNull($next);
        $this->assertTrue(is_a($next, Enrollee::class));
        $this->assertEquals($next->id, $enrollee1stPrio1->id);

        //call - perform action on enrollee (remove or reprioritise in queue)
        $this->performActionOnEnrollee($enrollee1stPrio1, Enrollee::REJECTED);
        //next patient - no family members - next enrollee should still be the second one confirmed for initial patient
        EnrollableCallQueue::update($this->careAmbassadorUser->careAmbassador, $enrollee1stPrio1, null);

        //1st Prio - 2nd confirmed family member
        $next = EnrollableCallQueue::getNext($this->careAmbassadorUser->careAmbassador);

        $this->assertNotNull($next);
        $this->assertTrue(is_a($next, Enrollee::class));
        $this->assertEquals($next->id, $enrollee1stPrio2->id);

        $this->performActionOnEnrollee($enrollee1stPrio2, Enrollee::REJECTED);
        //next patient - no family members - next enrollee should still be the second one confirmed for initial patient
        EnrollableCallQueue::update($this->careAmbassadorUser->careAmbassador, $enrollee1stPrio2, null);

        //2nd Prio
        $next = EnrollableCallQueue::getNext($this->careAmbassadorUser->careAmbassador);

        $this->assertNotNull($next);
        $this->assertTrue(is_a($next, Enrollee::class));
        $this->assertEquals($next->id, $enrollee2ndPrio->id);

        $this->performActionOnEnrollee($enrollee2ndPrio, Enrollee::REJECTED);

        //3rd Prio
        $next = EnrollableCallQueue::getNext($this->careAmbassadorUser->careAmbassador);

        $this->assertNotNull($next);
        $this->assertTrue(is_a($next, Enrollee::class));
        $this->assertEquals($next->id, $enrollee3rdPrio->id);

        $this->performActionOnEnrollee($enrollee3rdPrio, Enrollee::REJECTED);

        //4th Prio
        $next = EnrollableCallQueue::getNext($this->careAmbassadorUser->careAmbassador);

        $this->assertNotNull($next);
        $this->assertTrue(is_a($next, Enrollee::class));
        $this->assertEquals($next->id, $enrollee4thPrio->id);

        $this->performActionOnEnrollee($enrollee4thPrio, Enrollee::REJECTED);

        //5th Prio
        $next = EnrollableCallQueue::getNext($this->careAmbassadorUser->careAmbassador);

        $this->assertNotNull($next);
        $this->assertTrue(is_a($next, Enrollee::class));
        $this->assertEquals($next->id, $enrollee5thPrio->id);

        $this->performActionOnEnrollee($enrollee5thPrio, Enrollee::REJECTED);

        //6th Prio
        $next = EnrollableCallQueue::getNext($this->careAmbassadorUser->careAmbassador);

        $this->assertNotNull($next);
        $this->assertTrue(is_a($next, Enrollee::class));
        $this->assertEquals($next->id, $enrollee6thPrio->id);

        $this->performActionOnEnrollee($enrollee6thPrio, Enrollee::REJECTED);

        //After enrollees that in prio list, none should appear
        $next = EnrollableCallQueue::getNext($this->careAmbassadorUser->careAmbassador);

        $this->assertNull($next);
    }

    /**
     * Create these, assign to CA. They should never be viewed by CA.
     */
    private function createNeverToBeViewedEnrollees()
    {
        factory(Enrollee::class)->create([
            'practice_id'             => $this->practice->id,
            'care_ambassador_user_id' => $this->careAmbassadorUser->id,
            'status'                  => Enrollee::CONSENTED,
            //attempt count irrelevant
            'attempt_count' => 1,
        ]);

        factory(Enrollee::class)->create([
            'practice_id'             => $this->practice->id,
            'care_ambassador_user_id' => $this->careAmbassadorUser->id,
            'status'                  => Enrollee::ENROLLED,
            //attempt count irrelevant
            'attempt_count' => 1,
        ]);

        factory(Enrollee::class)->create([
            'practice_id'             => $this->practice->id,
            'care_ambassador_user_id' => $this->careAmbassadorUser->id,
            'status'                  => Enrollee::QUEUE_AUTO_ENROLLMENT,
            //attempt count irrelevant
            'attempt_count' => 0,
        ]);

        factory(Enrollee::class)->create([
            'practice_id'             => $this->practice->id,
            'care_ambassador_user_id' => $this->careAmbassadorUser->id,
            'status'                  => Enrollee::INELIGIBLE,
            //attempt count irrelevant
            'attempt_count' => 0,
        ]);

        factory(Enrollee::class)->create([
            'practice_id'             => $this->practice->id,
            'care_ambassador_user_id' => $this->careAmbassadorUser->id,
            'status'                  => Enrollee::SOFT_REJECTED,
            //attempt count irrelevant
            'attempt_count' => 1,
        ]);

        factory(Enrollee::class)->create([
            'practice_id'             => $this->practice->id,
            'care_ambassador_user_id' => $this->careAmbassadorUser->id,
            'status'                  => Enrollee::REJECTED,
            //attempt count irrelevant
            'attempt_count' => 1,
        ]);

        factory(Enrollee::class)->create([
            'practice_id'             => $this->practice->id,
            'care_ambassador_user_id' => $this->careAmbassadorUser->id,
            'status'                  => Enrollee::UNREACHABLE,
            //attempt NOT irrelevant - no unreachables with 3 tries and above should appear
            'attempt_count' => 3,
        ]);
    }
}
