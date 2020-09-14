<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\PostmarkInboundMail;
use App\Traits\Tests\PostmarkCallbackHelpers;
use App\Traits\Tests\PracticeHelpers;
use App\Traits\Tests\UserHelpers;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Database\Seeder;

class SeedInboundPostmarkCallbacks extends Seeder
{
    use PostmarkCallbackHelpers;
    use PracticeHelpers;
    use UserHelpers;
    private \CircleLinkHealth\Customer\Entities\User $careAmbassador;
    /**
     * @var User
     */
    private $patient;
    /**
     * @var Enrollee|\Illuminate\Database\Eloquent\Model
     */
    private $patientEnrollee;

    /**
     * @var \Illuminate\Database\Eloquent\Model|PostmarkInboundMail
     */
    private $postmarkRecord;
    private \CircleLinkHealth\Customer\Entities\Practice $practice;

    /**
     * @var \CircleLinkHealth\Customer\Entities\Practice
     */
    public function __construct()
    {
        $this->practice       = $this->setupPractice();
        $this->careAmbassador = $this->createUser($this->practice->id, 'care-ambassador');
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $n = 1;
        $this->createUsersOfTypeEnrolled($n, 5);
        $this->createUsersOfTypeNotEnrolled($n, 3);
        $this->createUsersOfTypeQueuedForEnrolmentButNotCAassigned($n, 7);
        $this->createUsersOfTypeNameIsSelf($n, 3);
        $this->createUsersOfTypeRequestedToWithdraw($n, 4);
        $this->createUsersOfTypeRequestedToWithdrawAndNameIsSelf($n, 6);
        $this->createUsersOfTypeResolvableMultiMatches($n, 2);
        $this->createUsersOfTypeNotResolvableMultiMatches($n, 2);
    }

    private function createUsersOfTypeEnrolled(int $n, int $limit)
    {
        while ($n <= $limit) {
            $this->createPatientData(Enrollee::ENROLLED);
            $this->command->info("Generated $n users out of $limit of type:[Enrollee::ENROLLED].");
            ++$n;
        }
    }

    private function createUsersOfTypeNameIsSelf(int $n, int $limit)
    {
        while ($n <= $limit) {
            $this->createPatientData(Patient::ENROLLED, false, true);
            $this->command->info("Generated $n users out of $limit of type:[Name Is SELF].");
            ++$n;
        }
    }

    private function createUsersOfTypeNotEnrolled(int $n, int $limit)
    {
        while ($n <= $limit) {
            $this->createPatientData(Patient::PAUSED);
            $this->command->info("Generated $n users out of $limit of type:[NOT ENROLLED.]");
            ++$n;
        }
    }

    private function createUsersOfTypeNotResolvableMultiMatches(int $n, int $limit)
    {
        while ($n <= $limit) {
            $this->createPatientData(Patient::ENROLLED);
            $this->patient->phoneNumbers
                ->first()
                ->update(
                    [
                        'number' => 1234567890,
                    ]
                );
            $this->patient->display_name = 'Hambis Flouretzou';
            $this->patient->first_name   = 'Hambis';
            $this->patient->last_name    = 'Flouretzou';
            $this->patient->save();
            $this->command->info("Generated $n users out of $limit of type:[NOT Eligible Multi Matches].");
            ++$n;
        }
    }

    private function createUsersOfTypeQueuedForEnrolmentButNotCAassigned(int $n, int $limit)
    {
        while ($n <= $limit) {
            $this->createPatientData(Enrollee::QUEUE_AUTO_ENROLLMENT);
            $this->patientEnrollee->update(
                [
                    'care_ambassador_user_id' => null,
                ]
            );
            $this->command->info("Generated $n users out of $limit of type:[Queued for self enrolment but not CA assigned].");
            ++$n;
        }
    }

    private function createUsersOfTypeRequestedToWithdraw(int $n, int $limit)
    {
        while ($n <= $limit) {
            $this->createPatientData(Patient::ENROLLED, true, false);
            $this->command->info("Generated $n users out of $limit of type:[Requested To Withdraw].");
            ++$n;
        }
    }

    private function createUsersOfTypeRequestedToWithdrawAndNameIsSelf(int $n, int $limit)
    {
        while ($n <= $limit) {
            $this->createPatientData(Patient::ENROLLED, true, true);
            $this->command->info("Generated $n users out of $limit of type Requested To Withdraw And Name Is SELF.");
            ++$n;
        }
    }

    private function createUsersOfTypeResolvableMultiMatches(int $n, int $limit)
    {
        while ($n <= $limit) {
            $this->createPatientData(Patient::ENROLLED);
            $this->patient->phoneNumbers
                ->first()
                ->update(
                    [
                        'number' => 1234567890,
                    ]
                );
            $this->command->info("Generated $n users out of $limit of type:[Eligible Multi Matches].");
            ++$n;
        }
    }
}
