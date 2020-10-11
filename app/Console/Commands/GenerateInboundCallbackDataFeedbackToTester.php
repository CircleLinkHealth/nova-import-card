<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\PostmarkInboundMail;
use App\Traits\Tests\PostmarkCallbackHelpers;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Traits\PracticeHelpers;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Console\Command;

class GenerateInboundCallbackDataFeedbackToTester extends Command
{
    use PostmarkCallbackHelpers;
    use PracticeHelpers;
    use UserHelpers;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates data and returns json field arrays to console for tester to work with';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:inboundCallbackData {userType}';
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
     * @var array|string|null
     */
    private $userType;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->practice       = $this->nekatostrasPractice();
        $this->careAmbassador = $this->createUser($this->practice->id, 'care-ambassador');
    }

    public function handle()
    {
        $this->userType = $this->argument('userType');

        if ( ! $this->userType) {
            $this->error('Please enter user type.');
        }

        $inboundData = ['No callback data'];

        if ($this->isTrue('enrolled')) {
            $inboundData = $this->createUsersOfTypeEnrolled(2);
        }

        if ($this->isTrue('not_enrolled')) {
            $inboundData = $this->createUsersOfTypeConsentedNotEnrolled(2);
        }

        if ($this->isTrue('queued_for_self_enrolment_but_ca_unassigned')) {
            $inboundData = $this->createUsersOfTypeQueuedForEnrolmentButNotCAassigned(2);
        }

        if ($this->isTrue('inbound_callback_name_is_self')) {
            $inboundData = $this->createUsersOfTypeNameIsSelf(2);
        }

        if ($this->isTrue('patient_requests_to_withdraw')) {
            $inboundData = $this->createUsersOfTypeRequestedToWithdraw(2);
        }

        if ($this->isTrue('patient_requests_to_withdraw_and_name_is_self')) {
            $inboundData = $this->createUsersOfTypeRequestedToWithdrawAndNameIsSelf(2);
        }

        if ($this->isTrue('phone_number_will_not_match_but_will_match_by_name')) {
            $inboundData = $this->matchableByNameNotPhone(2);
        }

        if ($this->isTrue('phone_number_and_name_will_not_match')) { // will send slack
            $inboundData = $this->createUsersOfTypeNotResolvableMultiMatches(2);
        }

        if ($this->isTrue('not_consented_ca_assigned')) {
            $inboundData = $this->createUsersOfTypeNotConsentedAssignedToCa(2);
        }

        if ($this->isTrue('not_consented_ca_unassigned')) {
            $inboundData = $this->createUsersOfTypeNotConsentedUnassignedCa(2);
        }

        if ($this->isTrue('patients_have_same_number_same_phone')) {
            $inboundData = $this->matchedPatientsSameNumberName(2);
        }

        $this->info(implode(", \n", $inboundData));
    }

    private function createUsersOfTypeConsentedNotEnrolled(int $limit)
    {
        $n           = 1;
        $inboundData = collect();
        while ($n <= $limit) {
            $this->createPatientData(Enrollee::CONSENTED);
            $this->generatePostmarkCallbackData(false, false);
            $this->info("Generated $n users out of $limit of type:[CONSENTED BUT NOT ENROLLED.]");
            $inboundData->push($this->postmarkRecord);
            ++$n;
        }

        return $inboundData->toArray();
    }

    /**
     * @return array
     */
    private function createUsersOfTypeEnrolled(int $limit)
    {
        $n           = 1;
        $inboundData = collect();
        while ($n <= $limit) {
            $this->createPatientData(Enrollee::ENROLLED);
            $this->generatePostmarkCallbackData(false, false);
            $this->info("Generated $n users out of $limit of type:[ENROLLED].");
            $inboundData->push($this->postmarkRecord);
            ++$n;
        }

        return $inboundData->toArray();
    }

    private function createUsersOfTypeNameIsSelf(int $limit)
    {
        $n           = 1;
        $inboundData = collect();
        while ($n <= $limit) {
            $this->createPatientData(Patient::ENROLLED);
            $this->generatePostmarkCallbackData(false, true);
            $this->info("Generated $n users out of $limit of type:[Name Is SELF].");
            $inboundData->push($this->postmarkRecord);
            ++$n;
        }

        return $inboundData->toArray();
    }

    private function createUsersOfTypeNotConsentedAssignedToCa(int $limit)
    {
        $n           = 1;
        $inboundData = collect();
        while ($n <= $limit) {
            $this->createPatientData(Enrollee::ELIGIBLE);
            $this->generatePostmarkCallbackData(false, false);
            $this->info("Generated $n users out of $limit of type:[NOT CONSENTED BUT CA ASSIGNED.]");
            $inboundData->push($this->postmarkRecord);
            ++$n;
        }

        return $inboundData->toArray();
    }

    private function createUsersOfTypeNotConsentedUnassignedCa(int $limit)
    {
        $n           = 1;
        $inboundData = collect();
        while ($n <= $limit) {
            $this->createPatientData(Enrollee::ELIGIBLE);

            $this->patientEnrollee->update(
                [
                    'care_ambassador_user_id' => null,
                ]
            );

            $this->generatePostmarkCallbackData(false, false);
            $this->info("Generated $n users out of $limit of type:[NOT CONSENTED AND CA UNASSIGNED.]");
            $inboundData->push($this->postmarkRecord);
            ++$n;
        }

        return $inboundData->toArray();
    }

    private function createUsersOfTypeNotResolvableMultiMatches(int $limit)
    {
        $n           = 1;
        $inboundData = collect();
        while ($n <= $limit) {
            $this->createPatientData(Patient::ENROLLED);
            $this->generatePostmarkCallbackData(false, false);
//            Changing the phone number and names to cause not finding results
            $this->patient->phoneNumbers
                ->first()
                ->update(
                    [
                        'number' => 1234567890,
                    ]
                );
            
            $this->patient->update([
                'display_name' => 'Mario Yianouko',
                'first_name'   => 'Mario',
                'last_name'    => 'Yianouko',
            ]);
            
            $this->info("Generated $n users out of $limit of type:[NOT Eligible Multi Matches].");
            $inboundData->push($this->postmarkRecord);
            ++$n;
        }

        return $inboundData->toArray();
    }

    private function createUsersOfTypeQueuedForEnrolmentButNotCAassigned(int $limit)
    {
        $inboundData = collect();
        $n           = 1;
        while ($n <= $limit) {
            $this->createPatientData(Enrollee::QUEUE_AUTO_ENROLLMENT);
            $this->generatePostmarkCallbackData(false, false);
            $this->patientEnrollee->update(
                [
                    'care_ambassador_user_id' => null,
                ]
            );
            $this->info("Generated $n users out of $limit of type:[Queued for self enrolment but not CA assigned].");
            $inboundData->push($this->postmarkRecord);
            ++$n;
        }

        return $inboundData->toArray();
    }

    private function createUsersOfTypeRequestedToWithdraw(int $limit)
    {
        $n           = 1;
        $inboundData = collect();
        while ($n <= $limit) {
            $this->createPatientData(Patient::ENROLLED);
            $this->generatePostmarkCallbackData(true, false);
            $this->info("Generated $n users out of $limit of type:[Requested To Withdraw].");
            $inboundData->push($this->postmarkRecord);
            ++$n;
        }

        return $inboundData->toArray();
    }

    private function createUsersOfTypeRequestedToWithdrawAndNameIsSelf(int $limit)
    {
        $n           = 1;
        $inboundData = collect();
        while ($n <= $limit) {
            $this->createPatientData(Patient::ENROLLED);
            $this->generatePostmarkCallbackData(true, true);
            $this->info("Generated $n users out of $limit of type Requested To Withdraw And Name Is SELF.");
            $inboundData->push($this->postmarkRecord);
            ++$n;
        }

        return $inboundData->toArray();
    }

    /**
     * @return bool
     */
    private function isTrue(string $constType)
    {
        return $this->userType === $constType;
    }

    private function matchableByNameNotPhone(int $limit)
    {
        $n           = 1;
        $inboundData = collect();
        while ($n <= $limit) {
            $this->createPatientData(Patient::ENROLLED);
            $this->generatePostmarkCallbackData(false, false);

            $this->patient->phoneNumbers
                ->first()
                ->update(
                    [
                        'number' => 1234567890,
                    ]
                );
            $this->info("Generated $n users out of $limit of type:[Eligible Multi Matches].");
            $inboundData->push($this->postmarkRecord);
            ++$n;
        }

        return $inboundData->toArray();
    }

    private function matchedPatientsSameNumberName(int $limit)
    {
        $n           = 1;
        $inboundData = collect();
        while ($n <= $limit) {
            $newNumber = '1234567890';

            $this->createPatientData(Patient::ENROLLED);
            $this->patient->phoneNumbers
                ->first()
                ->update(
                    [
                        'number' => $newNumber,
                    ]
                );

            $this->patient->update([
                'display_name' => 'Mario Yianouko',
                'first_name'   => 'Mario',
                'last_name'    => 'Yianouko',
            ]);

            $this->patient->fresh();
            $this->generatePostmarkCallbackData(false, false);

            $this->info("Generated $n users out of $limit of type:[NOT Eligible Multi Matches].");
            $inboundData->push($this->postmarkRecord);
            ++$n;
        }

        return $inboundData->toArray();
    }
}
