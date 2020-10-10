<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\PostmarkInboundMail;
use App\Traits\Tests\PostmarkCallbackHelpers;
use App\Traits\Tests\PracticeHelpers;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Database\Seeder;

class InboundPostmarkCallbackDataSeeder extends Seeder
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
        $this->practice       = $this->nekatostrasPractice();
        $this->careAmbassador = $this->createUser($this->practice->id, 'care-ambassador');
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->createUsersOfTypeEnrolled(3);
        $this->createUsersOfTypeConsentedNotEnrolled(3);
        $this->createUsersOfTypeNotConsentedAssignedToCa(3);
        $this->createUsersOfTypeQueuedForEnrolmentButNotCAassigned(3);
        $this->createUsersOfTypeNameIsSelf(3);
        $this->createUsersOfTypeRequestedToWithdraw(3);
        $this->createUsersOfTypeRequestedToWithdrawAndNameIsSelf(3);
        $this->createUsersOfTypeResolvableMultiMatches(3);
        $this->createUsersOfTypeNotResolvableMultiMatches(3);
    }
    private function createUsersOfTypeNotConsentedUnassignedCa(int $limit)
    {
        $n = 1;
        while ($n <= $limit) {
            $this->createPatientData(Enrollee::ELIGIBLE);
            
            $this->patientEnrollee->update(
                [
                    'care_ambassador_user_id' => null,
                ]
            );
            
            $this->createPostmarkCallbackData(false, false);
            $this->command->info("Generated $n users out of $limit of type:[NOT CONSENTED AND CA UNASSIGNED.]");
            ++$n;
        }
    }
    
    private function createUsersOfTypeConsentedNotEnrolled(int $limit)
    {
        $n = 1;
        while ($n <= $limit) {
            $this->createPatientData(Enrollee::CONSENTED);
            $this->createPostmarkCallbackData(false, false);
            $this->command->info("Generated $n users out of $limit of type:[NOT ENROLLED.]");
            ++$n;
        }
    }

    private function createUsersOfTypeEnrolled(int $limit)
    {
        $n = 1;
        while ($n <= $limit) {
            $this->createPatientData(Enrollee::ENROLLED);
            $this->createPostmarkCallbackData(false, false);
            $this->command->info("Generated $n users out of $limit of type:[ENROLLED].");
            ++$n;
        }
    }

    private function createUsersOfTypeNameIsSelf(int $limit)
    {
        $n = 1;
        while ($n <= $limit) {
            $this->createPatientData(Patient::ENROLLED);
            $this->createPostmarkCallbackData(false, true);
            $this->command->info("Generated $n users out of $limit of type:[Name Is SELF].");
            ++$n;
        }
    }

    private function createUsersOfTypeNotConsentedAssignedToCa(int $limit)
    {
        $n = 1;
        while ($n <= $limit) {
            $this->createPatientData(Enrollee::ELIGIBLE);
            $this->createPostmarkCallbackData(false, false);
            $this->command->info("Generated $n users out of $limit of type:[NOT CONSENTED BUT CA ASSIGNED.]");
            ++$n;
        }
    }
  
    
    private function createUsersOfTypeNotResolvableMultiMatches(int $limit)
    {
        $n = 1;
        while ($n <= $limit) {
            $this->createPatientData(Patient::ENROLLED);
            $this->createPostmarkCallbackData(false, false);
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

    private function createUsersOfTypeQueuedForEnrolmentButNotCAassigned(int $limit)
    {
        $n = 1;
        while ($n <= $limit) {
            $this->createPatientData(Enrollee::QUEUE_AUTO_ENROLLMENT);
            $this->createPostmarkCallbackData(false, false);
            $this->patientEnrollee->update(
                [
                    'care_ambassador_user_id' => null,
                ]
            );
            $this->command->info("Generated $n users out of $limit of type:[Queued for self enrolment but not CA assigned].");
            ++$n;
        }
    }

    private function createUsersOfTypeRequestedToWithdraw(int $limit)
    {
        $n = 1;
        while ($n <= $limit) {
            $this->createPatientData(Patient::ENROLLED);
            $this->createPostmarkCallbackData(true, false);
            $this->command->info("Generated $n users out of $limit of type:[Requested To Withdraw].");
            ++$n;
        }
    }

    private function createUsersOfTypeRequestedToWithdrawAndNameIsSelf(int $limit)
    {
        $n = 1;
        while ($n <= $limit) {
            $this->createPatientData(Patient::ENROLLED);
            $this->createPostmarkCallbackData(true, true);
            $this->command->info("Generated $n users out of $limit of type Requested To Withdraw And Name Is SELF.");
            ++$n;
        }
    }

    private function createUsersOfTypeResolvableMultiMatches(int $limit)
    {
        $n = 1;
        while ($n <= $limit) {
            $this->createPatientData(Patient::ENROLLED);
            $this->createPostmarkCallbackData(false, false);
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
