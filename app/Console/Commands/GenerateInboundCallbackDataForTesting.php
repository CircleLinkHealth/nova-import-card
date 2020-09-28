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

class GenerateInboundCallbackDataForTesting extends Command
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
        $this->practice       = $this->setupPractice();
        $this->careAmbassador = $this->createUser($this->practice->id, 'care-ambassador');
    }

    public function handle()
    {
        $this->userType = $this->argument('userType');

        if ( ! $this->userType) {
            $this->error('Please enter user type.');
        }

        $inboundData = ['No callback data'];

        if ($this->isTrue(Enrollee::ENROLLED)) {
            $inboundData = $this->createUsersOfTypeEnrolled(2);
        }

        if ($this->isTrue(Patient::PAUSED)) {
            $inboundData = $this->createUsersOfTypeNotEnrolled(2);
        }

        if ($this->isTrue('queued_ca_unassigned')) {
            $inboundData = $this->createUsersOfTypeQueuedForEnrolmentButNotCAassigned(2);
        }

        if ($this->isTrue('callback_name_self')) {
            $inboundData = $this->createUsersOfTypeNameIsSelf(2);
        }

        if ($this->isTrue('withdraw_request')) {
            $inboundData = $this->createUsersOfTypeRequestedToWithdraw(2);
        }

        if ($this->isTrue('requests_withdraw_name_self')) {
            $inboundData = $this->createUsersOfTypeRequestedToWithdrawAndNameIsSelf(2);
        }

        if ($this->isTrue('resolvable_multimatch')) {
            $inboundData = $this->createUsersOfTypeResolvableMultiMatches(2);
        }

        if ($this->isTrue('unresolvable_multimatch')) {
            $inboundData = $this->createUsersOfTypeNotResolvableMultiMatches(2);
        }

        $this->info(implode(", \n", $inboundData));
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

    private function createUsersOfTypeNotEnrolled(int $limit)
    {
        $inboundData = collect();
        $n           = 1;
        while ($n <= $limit) {
            $this->createPatientData(Patient::PAUSED);
            $this->generatePostmarkCallbackData(false, false);
            $this->info("Generated $n users out of $limit of type:[NOT ENROLLED.]");
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

    private function createUsersOfTypeResolvableMultiMatches(int $limit)
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

    /**
     * @return bool
     */
    private function isTrue(string $constType)
    {
        return $this->userType === $constType;
    }
}
