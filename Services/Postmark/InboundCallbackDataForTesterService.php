<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Services\Postmark;

use CircleLinkHealth\Core\Console\Commands\GenerateInboundCallbackDataFeedbackToTester;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use CircleLinkHealth\SharedModels\Traits\Tests\PostmarkCallbackHelpers;
use Faker\Factory;

class InboundCallbackDataForTesterService
{
    use PostmarkCallbackHelpers;
    use UserHelpers;
    private User $careAmbassador;

    /**
     * @var \CircleLinkHealth\Customer\Entities\Practice|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    private $practice;

    public function __construct()
    {
        $this->practice       = $this->practiceForSeeding();
        $this->careAmbassador = $this->createUser($this->practice->id, 'care-ambassador');
    }

    /**
     * @return array/**
     */
    public function createUsersOfTypeConsentedButNotEnrolled(bool $save = false)
    {
        return $this->dataOfStatusType(Patient::TO_ENROLL, false, false, $save, Enrollee::CONSENTED);
    }

    /**
     * @return array
     */
    public function createUsersOfTypeEnrolled(bool $save = false)
    {
        return $this->dataOfStatusType(Patient::ENROLLED, false, false, $save, Enrollee::ENROLLED);
    }

    /**
     * @return array
     */
    public function createUsersOfTypeNameIsSelf(bool $save = false)
    {
        return $this->dataOfStatusType(Patient::ENROLLED, false, true, $save, Enrollee::ENROLLED);
    }

    /**
     * @return array
     */
    public function createUsersOfTypeNotConsentedAssignedToCa(bool $save = false)
    {
        return $this->dataOfStatusType(Patient::PAUSED, false, false, $save, Enrollee::ELIGIBLE);
    }

    /**
     * @return array
     */
    public function createUsersOfTypeNotConsentedUnassignedCa(bool $save = false)
    {
        return $this->dataOfTypeNotConsentedCaUnassigned(Patient::PAUSED, false, false, $save);
    }

    /**
     * @return array
     */
    public function createUsersOfTypeQueuedForEnrolmentButNotCAssigned(bool $save = false)
    {
        return $this->dataOfTypeSelfEnrollableCaUnassigned(Patient::TO_ENROLL, false, false, $save);
    }

    /**
     * @return array
     */
    public function createUsersOfTypeRequestedToWithdraw(bool $save = false)
    {
        return $this->dataOfStatusType(Patient::ENROLLED, true, false, $save, Enrollee::ENROLLED);
    }

    /**
     * @return array
     */
    public function createUsersOfTypeRequestedToWithdrawAndNameIsSelf(bool $save = false)
    {
        return $this->dataOfStatusType(Patient::ENROLLED, true, true, $save, Enrollee::ENROLLED);
    }

    /**
     * @return array
     */
    public function dataOfStatusType(string $patientType, bool $requestToWithdraw, bool $nameIsSelf, bool $save = false, string $enrolleeType)
    {
        $n           = GenerateInboundCallbackDataFeedbackToTester::START;
        $inboundData = collect();
        while ($n <= GenerateInboundCallbackDataFeedbackToTester::LIMIT) {
            $patient        = $this->createPatientData($patientType, $this->practice->id, $enrolleeType);
            $postmarkRecord = $save
                ? $this->createPostmarkCallbackData($requestToWithdraw, $nameIsSelf, $patient)
                : $this->generatePostmarkCallbackData($requestToWithdraw, $nameIsSelf, $patient);

            $inboundData->push($postmarkRecord);
            ++$n;
        }

        return $inboundData->toArray();
    }

    /**
     * @return array
     */
    public function dataOfTypeNotConsentedCaUnassigned(string $patientType, bool $requestToWithdraw, bool $nameIsSelf, bool $save)
    {
        $n           = GenerateInboundCallbackDataFeedbackToTester::START;
        $inboundData = collect();
        while ($n <= GenerateInboundCallbackDataFeedbackToTester::LIMIT) {
            $patient = $this->createPatientData($patientType, $this->practice->id, Enrollee::ELIGIBLE);
            $patient->enrollee->update(
                [
                    'care_ambassador_user_id' => null,
                ]
            );

            $postmarkRecord = $save
                ? $this->createPostmarkCallbackData($requestToWithdraw, $nameIsSelf, $patient)
                : $this->generatePostmarkCallbackData($requestToWithdraw, $nameIsSelf, $patient);
            $inboundData->push($postmarkRecord);
            ++$n;
        }

        return $inboundData->toArray();
    }

    /**
     * @return array
     */
    public function dataOfTypeSamePhoneAndName(string $patientType, bool $requestToWithdraw, bool $nameIsSelf, bool $save)
    {
        $n         = GenerateInboundCallbackDataFeedbackToTester::START;
        $faker     = Factory::create();
        $firstName = $faker->firstName;
        $lastName  = $faker->lastName;
        $phone     = $faker->phoneNumber;

        $inboundData = collect();
        while ($n <= GenerateInboundCallbackDataFeedbackToTester::LIMIT) {
            $patient = $this->createPatientData($patientType, $this->practice->id, Enrollee::ENROLLED);
            $patient->phoneNumbers
                ->first()
                ->update(
                    [
                        'number' => $phone,
                    ]
                );

            $patient->update([
                'display_name' => $firstName.' '.$lastName,
                'first_name'   => $firstName,
                'last_name'    => $lastName,
            ]);

            $patient->fresh();
            $postmarkRecord = $save
                ? $this->createPostmarkCallbackData($requestToWithdraw, $nameIsSelf, $patient)
                : $this->generatePostmarkCallbackData($requestToWithdraw, $nameIsSelf, $patient);

            if ($inboundData->isEmpty()) {
                $inboundData->push($postmarkRecord);
            }

            ++$n;
        }

        return $inboundData->toArray();
    }

    /**
     * @return array
     */
    public function dataOfTypeSelfEnrollableCaUnassigned(string $patientType, bool $requestToWithdraw, bool $nameIsSelf, bool $save)
    {
        $n           = GenerateInboundCallbackDataFeedbackToTester::START;
        $inboundData = collect();
        while ($n <= GenerateInboundCallbackDataFeedbackToTester::LIMIT) {
            $patient        = $this->createPatientData($patientType, $this->practice->id, Enrollee::QUEUE_AUTO_ENROLLMENT);
            $postmarkRecord = $save
                ? $this->createPostmarkCallbackData($requestToWithdraw, $nameIsSelf, $patient)
                : $this->generatePostmarkCallbackData($requestToWithdraw, $nameIsSelf, $patient);
            $patient->enrollee->update(
                [
                    'care_ambassador_user_id' => null,
                ]
            );

            $inboundData->push($postmarkRecord);
            ++$n;
        }

        return $inboundData->toArray();
    }

    /**
     * @return array/**
     *
     */
    public function dataOfTypeUnmatchable(string $patientType, bool $requestToWithdraw, bool $nameIsSelf, bool $save)
    {
        $n         = GenerateInboundCallbackDataFeedbackToTester::START;
        $faker     = Factory::create();
        $firstName = $faker->firstName;
        $lastName  = $faker->lastName;
        $phone     = $faker->phoneNumber;

        $inboundData = collect();
        while ($n <= GenerateInboundCallbackDataFeedbackToTester::LIMIT) {
            $patient        = $this->createPatientData($patientType, $this->practice->id, Enrollee::ENROLLED);
            $postmarkRecord = $save
                ? $this->createPostmarkCallbackData($requestToWithdraw, $nameIsSelf, $patient)
                : $this->generatePostmarkCallbackData($requestToWithdraw, $nameIsSelf, $patient);
            $patient->phoneNumbers
                ->first()
                ->update(
                    [
                        'number' => $phone,
                    ]
                );

            $patient->update([
                'display_name' => $firstName.' '.$lastName,
                'first_name'   => $firstName,
                'last_name'    => $lastName,
            ]);

            $inboundData->push($postmarkRecord);
            ++$n;
        }

        return $inboundData->toArray();
    }

    /**
     * @return array
     */
    public function multiMatchPatientsWithSameNumberAndName(bool $save = false)
    {
        return $this->dataOfTypeSamePhoneAndName(Patient::ENROLLED, false, false, $save);
    }

    public function noMatch(bool $save = false)
    {
        return $this->dataOfTypeUnmatchable(Patient::ENROLLED, false, false, $save);
    }

    /**
     * @return \Collection|\Illuminate\Support\Collection
     */
    public function runAllFunctions()
    {
        $generatedPostmarkIds = collect();
        $generatedPostmarkIds->push(...collect($this->createUsersOfTypeEnrolled(true))->pluck('id'));
        $generatedPostmarkIds->push(...collect($this->createUsersOfTypeConsentedButNotEnrolled(true))->pluck('id'));
        $generatedPostmarkIds->push(...collect($this->createUsersOfTypeQueuedForEnrolmentButNotCAssigned(true))->pluck('id'));
        $generatedPostmarkIds->push(...collect($this->createUsersOfTypeNameIsSelf(true))->pluck('id'));
        $generatedPostmarkIds->push(...collect($this->createUsersOfTypeRequestedToWithdraw(true))->pluck('id'));
        $generatedPostmarkIds->push(...collect($this->createUsersOfTypeRequestedToWithdrawAndNameIsSelf(true))->pluck('id'));
        $generatedPostmarkIds->push(...collect($this->createUsersOfTypeNotConsentedAssignedToCa(true))->pluck('id'));
        $generatedPostmarkIds->push(...collect($this->createUsersOfTypeNotConsentedUnassignedCa(true))->pluck('id'));
        $generatedPostmarkIds->push(...collect($this->multiMatchPatientsWithSameNumberAndName(true))->pluck('id'));
        $generatedPostmarkIds->push(...collect($this->noMatch(true))->pluck('id'));

        return $generatedPostmarkIds;
    }
}
