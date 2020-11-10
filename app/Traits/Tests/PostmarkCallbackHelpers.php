<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits\Tests;

use App\Jobs\ProcessPostmarkInboundMailJob;
use App\PostmarkInboundMail;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Support\Facades\App;

trait PostmarkCallbackHelpers
{
    public function getCallbackMailData(User $patient, bool $requestsToWithdraw, bool $nameIsSelf = false)
    {
        $this->phone = $patient->phoneNumbers->first();
        $number      = $this->phone->number;
        $name        = $nameIsSelf ? 'SELF' : $patient->display_name;

        $inboundMailDomain = ProcessPostmarkInboundMailJob::FROM_CALLBACK_EMAIL_DOMAIN;
        $primary           = $patient->getBillingProviderName() ?: 'Salah';
        $clrId             = $number.' '.$patient->display_name;

        $callbackMailData = "For: GROUP DISTRIBUTION
        From:| $inboundMailDomain |
        Phone:| $number |
        Ptn:| $name  |
        Primary:| $primary |
        Msg:| titolos pomolos lorem calypsum |
        Msg ID: Not relevant
        IS Rec #: Not relevant
        Clr ID: $clrId
        Taken: Not relevant";

        if ($requestsToWithdraw) {
            $withdrawReasonText = 'Cancel/Withdraw Reason:| I want to Cancel |';
            $extraValues        = "\n".' '.$withdrawReasonText;
            $callbackMailData   = $callbackMailData.$extraValues;
        }

        return $callbackMailData;
    }

    public function nekatostrasPractice()
    {
        if (isUnitTestingEnv()) {
            return Practice::firstOrFail();
        }
        
        if (App::environment('review')){
            return Practice::where('name', '=', \NekatostrasClinicSeeder::NEKATOSTRAS_PRACTICE)->firstOrFail();
        }
        
        return Practice::where('name', 'demo')->firstOrFail();
    }

    private function createEnrolleeData(string $status, User $patient, int $practiceId, int $careAmbassadorId)
    {
        return $this->createEnrolleeWithStatus($patient, $careAmbassadorId, $status, $practiceId);
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

    private function createPatientData(string $status, int $practiceId)
    {
        return $this->createUserWithPatientCcmStatus($practiceId, $status);
    }

    private function createPostmarkCallbackData(bool $requestToWithdraw, bool $nameIsSelf, User $patient)
    {
        return PostmarkInboundMail::create(
            [
                'data' => json_encode(
                    [
                        'From'     => 'message.dispatch@callcenterusa.net',
                        'TextBody' => $this->getCallbackMailData($patient, $requestToWithdraw, $nameIsSelf),
                    ]
                ),
            ]
        );
    }

    private function createUserWithPatientCcmStatus(int $practiceId, string $status)
    {
        $user = $this->createUser($practiceId, 'participant', $status);
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
        return $this->getCallbackMailData($patient, $requestToWithdraw, $nameIsSelf);
    }
}
