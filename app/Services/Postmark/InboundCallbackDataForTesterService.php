<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Postmark;

use App\Console\Commands\GenerateInboundCallbackDataFeedbackToTester;
use App\Traits\Tests\PostmarkCallbackHelpers;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
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
        return $this->dataOfStatusType(Enrollee::CONSENTED, false, false, $save);
    }

    /**
     * @return array
     */
    public function createUsersOfTypeEnrolled(bool $save = false)
    {
        return $this->dataOfStatusType(Enrollee::ENROLLED, false, false, $save);
    }

    /**
     * @return array
     */
    public function createUsersOfTypeNameIsSelf(bool $save = false)
    {
        return $this->dataOfStatusType(Enrollee::ENROLLED, false, true, $save);
    }

    /**
     * @return array
     */
    public function createUsersOfTypeNotConsentedAssignedToCa(bool $save = false)
    {
        return $this->dataOfStatusType(Enrollee::ELIGIBLE, false, false, $save);
    }

    /**
     * @return array
     */
    public function createUsersOfTypeNotConsentedUnassignedCa(bool $save = false)
    {
        return $this->dataOfTypeNotConsentedCaUnassigned(Enrollee::ELIGIBLE, false, false, $save);
    }

    /**
     * @return array
     */
    public function createUsersOfTypeQueuedForEnrolmentButNotCAssigned(bool $save = false)
    {
        return $this->dataOfTypeSelfEnrollableCaUnassigned(Enrollee::QUEUE_AUTO_ENROLLMENT, false, false, $save);
    }

    /**
     * @return array
     */
    public function createUsersOfTypeRequestedToWithdraw(bool $save = false)
    {
        return $this->dataOfStatusType(Enrollee::ENROLLED, true, false, $save);
    }

    /**
     * @return array
     */
    public function createUsersOfTypeRequestedToWithdrawAndNameIsSelf(bool $save = false)
    {
        return $this->dataOfStatusType(Enrollee::ENROLLED, true, true, $save);
    }

    /**
     * @return array
     */
    public function dataOfStatusType(string $patientType, bool $requestToWithdraw, bool $nameIsSelf, bool $save = false)
    {
        $n           = GenerateInboundCallbackDataFeedbackToTester::START;
        $inboundData = collect();
        while ($n <= GenerateInboundCallbackDataFeedbackToTester::LIMIT) {
            $patient        = $this->createPatientData($patientType, $this->practice->id);
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
            $patient = $this->createPatientData($patientType, $this->practice->id);
            $this->createEnrolleeData($patientType, $patient, $this->practice->id, $this->careAmbassador->id)->update(
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
            $patient = $this->createPatientData($patientType, $this->practice->id);
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
            $patient        = $this->createPatientData($patientType, $this->practice->id);
            $postmarkRecord = $save
                ? $this->createPostmarkCallbackData($requestToWithdraw, $nameIsSelf, $patient)
                : $this->generatePostmarkCallbackData($requestToWithdraw, $nameIsSelf, $patient);
            $this->createEnrolleeData($patientType, $patient, $this->practice->id, $this->careAmbassador->id)->update(
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
            $patient        = $this->createPatientData($patientType, $this->practice->id);
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
        return $this->dataOfTypeSamePhoneAndName(Enrollee::ENROLLED, false, false, $save);
    }

    public function noMatch(bool $save = false)
    {
        return $this->dataOfTypeUnmatchable(Enrollee::ENROLLED, false, false, $save);
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
