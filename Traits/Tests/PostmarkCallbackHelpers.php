<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Traits\Tests;

use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\Customer\Database\Seeders\NekatostrasClinicSeeder;
use CircleLinkHealth\Customer\DTO\PostmarkCallbackInboundData;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use CircleLinkHealth\SharedModels\Entities\PostmarkInboundMail;

trait PostmarkCallbackHelpers
{
    /**
     * @param bool $testForUnsanitisedInputCases
     *
     * @return string
     */
    public function getCallbackMailData(User $patient, bool $requestsToWithdraw, bool $nameIsSelf = false, string $number, $testForUnsanitisedInputCases = false)
    {
        $name  = $nameIsSelf ? 'SELF' : $patient->display_name;
        $clrId = $number.' '.$patient->display_name;

        if ($testForUnsanitisedInputCases) {
            $name  = $name.' '.$patient->display_name.' '.'*';
            $clrId = $number.' '.$number.' '.'Pavlos Tsokkos';
        }

        $inboundMailDomain = CpmConstants::FROM_CALLBACK_EMAIL_DOMAIN;
        $primary           = $patient->getBillingProviderName() ?: 'Salah';

        $callbackMailData = "For: GROUP DISTRIBUTION
        From:| $inboundMailDomain |
        Phone:| $number |
        Ptn:| $name |
        Primary:| $primary |
        Msg:| titolos pomolos lorem calypsum |
        Msg ID: Not relevant
        IS Rec #: Not relevant
        Clr ID: $clrId
        Taken: Not relevant";

        if ($requestsToWithdraw) {
            $key                = PostmarkCallbackInboundData::CANCELLATION_REASON_KEY;
            $withdrawReasonText = "$key:| I want to Cancel |";
            $extraValues        = "\n".' '.$withdrawReasonText;
            $callbackMailData   = $callbackMailData.$extraValues;
        }

        return $callbackMailData;
    }

    public function practiceForSeeding(): Practice
    {
        if (isProductionEnv()) {
            throw new \Exception('Should not have reached here. You cannot run this seeder in Production.');
        }

        if (isUnitTestingEnv()) {
            return Practice::firstOrFail();
        }

        $practice = Practice::where('name', '=', NekatostrasClinicSeeder::NEKATOSTRAS_PRACTICE)->first();

        if ( ! $practice) {
            $practice = Practice::where('name', '=', 'demo')->firstOrFail();
        }

        return $practice;
    }

    private function createEnrolleeWithStatus(User $patient, int $careAmbassadorId, string $status, int $practiceId)
    {
        $provider = $this->createUser($practiceId, 'provider');

        $enrollee = Enrollee::create([
            'user_id'                 => $patient->id,
            'first_name'              => $patient->first_name,
            'last_name'               => $patient->last_name,
            'home_phone'              => formatPhoneNumberE164($patient->phoneNumbers->first()->number),
            'status'                  => $status,
            'care_ambassador_user_id' => $careAmbassadorId,
            'practice_id'             => $practiceId,
        ]);
        $enrollee->provider()->associate($provider);
        $enrollee->save();

        return $enrollee;
    }

    private function createPatientData(string $patientStatus, int $practiceId, string $enrolleeStatus, string $role = 'participant')
    {
        $patient = $this->createUserWithPatientCcmStatus($practiceId, $patientStatus, $role);
        $this->createEnrolleeWithStatus($patient, $this->careAmbassador->id, $enrolleeStatus, $this->practice->id);

        return $patient;
    }

    private function createPostmarkCallbackData(bool $requestToWithdraw, bool $nameIsSelf, User $patient, string $forcePhone = '', bool $testForUnsanitisedInputCases = false)
    {
        $this->phone = $forcePhone;
        $number      = $this->phone;

        if ('' === $this->phone) {
            $this->phone = $patient->phoneNumbers->first();
            $number      = $this->phone->number;
        }

        return PostmarkInboundMail::create(
            [
                'data' => json_encode(
                    [
                        'From'     => 'message.dispatch@callcenterusa.net',
                        'TextBody' => $this->getCallbackMailData($patient, $requestToWithdraw, $nameIsSelf, $number, $testForUnsanitisedInputCases),
                    ]
                ),
            ]
        );
    }

    private function createUserWithPatientCcmStatus(int $practiceId, string $status, string $role)
    {
        $user = $this->createUser($practiceId, $role, $status);
        $user->patientSummaries()->update([
            'no_of_successful_calls' => 0,
        ]);

        $user->patientSummaries->fresh();

        return $user;
    }

    /**
     * @return string
     */
    private function generatePostmarkCallbackData(bool $requestToWithdraw, bool $nameIsSelf, User $patient)
    {
        return $this->getCallbackMailData($patient, $requestToWithdraw, $nameIsSelf, $patient->phoneNumbers->first()->number);
    }
}
