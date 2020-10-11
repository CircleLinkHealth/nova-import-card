<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\PostmarkInboundMail;
use App\Traits\Tests\PostmarkCallbackHelpers;
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

    const LIMIT = 2;
    const START = 1;

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
    protected $signature = 'create:inboundCallbackData {userType} {--save}';
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
     * @var array|bool|string|null
     */
    private $save;
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

    /**
     * @return array
     */
    public function dataOfStatusType(string $patientType, bool $requestToWithdraw, bool $nameIsSelf)
    {
        $n           = self::START;
        $inboundData = collect();
        while ($n <= self::LIMIT) {
            $this->createPatientData($patientType);
            $this->save
                ? $this->createPostmarkCallbackData($requestToWithdraw, $nameIsSelf)
                : $this->generatePostmarkCallbackData($requestToWithdraw, $nameIsSelf);

            $inboundData->push($this->postmarkRecord);
            ++$n;
        }

        return $inboundData->toArray();
    }

    /**
     * @return array
     */
    public function dataOfTypeNotConsentedCaUnassigned(string $patientType, bool $requestToWithdraw, bool $nameIsSelf)
    {
        $n           = self::START;
        $inboundData = collect();
        while ($n <= self::LIMIT) {
            $this->createPatientData($patientType);
            $this->patientEnrollee->update(
                [
                    'care_ambassador_user_id' => null,
                ]
            );

            $this->save
                ? $this->createPostmarkCallbackData($requestToWithdraw, $nameIsSelf)
                : $this->generatePostmarkCallbackData($requestToWithdraw, $nameIsSelf);
            $inboundData->push($this->postmarkRecord);
            ++$n;
        }

        return $inboundData->toArray();
    }

    /**
     * @param  mixed $saveData
     * @return array
     */
    public function dataOfTypeSameNumber(string $patientType, bool $requestToWithdraw, bool $nameIsSelf)
    {
        $n           = self::START;
        $inboundData = collect();
        while ($n <= self::LIMIT) {
            $this->createPatientData($patientType);
            $this->save
                ? $this->createPostmarkCallbackData($requestToWithdraw, $nameIsSelf)
                : $this->generatePostmarkCallbackData($requestToWithdraw, $nameIsSelf);

            $this->patient->phoneNumbers
                ->first()
                ->update(
                    [
                        'number' => 1234567890,
                    ]
                );

            $inboundData->push($this->postmarkRecord);
            ++$n;
        }

        return $inboundData->toArray();
    }

    /**
     * @return array
     */
    public function dataOfTypeSamePhoneAndName(string $patientType, bool $requestToWithdraw, bool $nameIsSelf)
    {
        $n           = self::START;
        $inboundData = collect();
        while ($n <= self::LIMIT) {
            $newNumber = '1234567890';
            $this->createPatientData($patientType);
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
            $this->save
                ? $this->createPostmarkCallbackData($requestToWithdraw, $nameIsSelf)
                : $this->generatePostmarkCallbackData($requestToWithdraw, $nameIsSelf);
            $inboundData->push($this->postmarkRecord);
            ++$n;
        }

        return $inboundData->toArray();
    }

    /**
     * @return array
     */
    public function dataOfTypeSelfEnrollableCaUnassigned(string $patientType, bool $requestToWithdraw, bool $nameIsSelf)
    {
        $n           = self::START;
        $inboundData = collect();
        while ($n <= self::LIMIT) {
            $this->createPatientData($patientType);
            $this->save
                ? $this->createPostmarkCallbackData($requestToWithdraw, $nameIsSelf)
                : $this->generatePostmarkCallbackData($requestToWithdraw, $nameIsSelf);
            $this->patientEnrollee->update(
                [
                    'care_ambassador_user_id' => null,
                ]
            );

            $inboundData->push($this->postmarkRecord);
            ++$n;
        }

        return $inboundData->toArray();
    }

    /**
     * @return array/**
     *
     */
    public function dataOfTypeUnmatchable(string $patientType, bool $requestToWithdraw, bool $nameIsSelf)
    {
        $n           = self::START;
        $inboundData = collect();
        while ($n <= self::LIMIT) {
            $this->createPatientData($patientType);
            $this->save
                ? $this->createPostmarkCallbackData($requestToWithdraw, $nameIsSelf)
                : $this->generatePostmarkCallbackData($requestToWithdraw, $nameIsSelf);
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

            $inboundData->push($this->postmarkRecord);
            ++$n;
        }

        return $inboundData->toArray();
    }

    /**
     * /**
     *
     */
    public function handle()
    {
        $this->userType = $this->argument('userType');
        $this->save     = $this->option('save');

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
            $inboundData = $this->createUsersOfTypeQueuedForEnrolmentButNotCAssigned(2);
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

        if ( ! $this->save) {
            $this->info(implode(", \n", $inboundData));
        }
    }

    /**
     * @return array/**
     *
     */
    private function createUsersOfTypeConsentedNotEnrolled(int $limit)
    {
        $inboundData = $this->dataOfStatusType(Enrollee::CONSENTED, false, false);
        $this->info("$limit created of type:[CONSENTED BUT NOT ENROLLED.].");

        return $inboundData;
    }

    /**
     * @return array
     */
    private function createUsersOfTypeEnrolled(int $limit)
    {
        $inboundData = $this->dataOfStatusType(Enrollee::ENROLLED, false, false);
        $this->info("$limit patients created of type:[ENROLLED].");

        return $inboundData;
    }

    /**
     * @return array
     */
    private function createUsersOfTypeNameIsSelf(int $limit)
    {
        $inboundData = $this->dataOfStatusType(Enrollee::ENROLLED, false, true);
        $this->info("Generated $limit patients of type:[Name Is SELF].");

        return $inboundData;
    }

    /**
     * @return array
     */
    private function createUsersOfTypeNotConsentedAssignedToCa(int $limit)
    {
        $inboundData = $this->dataOfStatusType(Enrollee::ELIGIBLE, false, false);
        $this->info("Generated $limit patients of type:[NOT CONSENTED BUT CA ASSIGNED.]");

        return $inboundData;
    }

    /**
     * @return array
     */
    private function createUsersOfTypeNotConsentedUnassignedCa(int $limit)
    {
        $inboundData = $this->dataOfStatusType(Enrollee::ELIGIBLE, false, false);
        $this->info("Generated $limit patients of type:[NOT CONSENTED AND CA NOT ASSIGNED.]");

        return $inboundData;
    }

    /**
     * @return array
     */
    private function createUsersOfTypeNotResolvableMultiMatches(int $limit)
    {
        $inboundData = $this->dataOfTypeSelfEnrollableCaUnassigned(Enrollee::ENROLLED, false, false);
        $this->info("Generated $limit patients of type:[NOT Eligible Multi Matches].");

        return $inboundData;
    }

    /**
     * @return array
     */
    private function createUsersOfTypeQueuedForEnrolmentButNotCAssigned(int $limit)
    {
        $inboundData = $this->dataOfTypeSelfEnrollableCaUnassigned(Enrollee::QUEUE_AUTO_ENROLLMENT, false, false);
        $this->info("Generated $limit patients of type:[Queued for self enrolment but not CA assigned].");

        return $inboundData;
    }

    /**
     * @return array
     */
    private function createUsersOfTypeRequestedToWithdraw(int $limit)
    {
        $inboundData = $this->dataOfStatusType(Enrollee::ENROLLED, true, false);
        $this->info("Generated $limit patients of type:[Requested To Withdraw].");

        return $inboundData;
    }

    /**
     * @return array
     */
    private function createUsersOfTypeRequestedToWithdrawAndNameIsSelf(int $limit)
    {
        $inboundData = $this->dataOfStatusType(Enrollee::ENROLLED, true, true);
        $this->info("Generated $limit patients of type Requested To Withdraw And Name Is SELF.");

        return $inboundData;
    }

    /**
     * @return bool
     */
    private function isTrue(string $constType)
    {
        return $this->userType === $constType;
    }

    /**
     * @return array
     */
    private function matchableByNameNotPhone(int $limit)
    {
        $inboundData = $this->dataOfTypeSameNumber(Enrollee::ENROLLED, false, false);
        $this->info("Generated $limit patient of type:[Patients with same number different name].");

        return $inboundData;
    }

    /**
     * @return array
     */
    private function matchedPatientsSameNumberName(int $limit)
    {
        $inboundData = $this->dataOfTypeSameNumber(Enrollee::ENROLLED, false, false);
        $this->info("Generated $limit patients of type:[Same name and number].");

        return $inboundData;
    }
}
