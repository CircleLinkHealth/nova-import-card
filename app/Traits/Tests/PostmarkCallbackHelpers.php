<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits\Tests;

use App\Algorithms\Calls\NurseFinder\NurseFinderEloquentRepository;
use App\Jobs\ProcessPostmarkInboundMailJob;
use App\PostmarkInboundMail;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;

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

        return Practice::where('name', '=', \NekatostrasClinicSeeder::NEKATOSTRAS_PRACTICE)->firstOrFail();
    }

    private function createEnrolleeWithStatus(User $patient, int $careAmbassadorId, string $status)
    {
        $provider = $this->createUser($this->practice->id, 'provider');

        $enrollee = Enrollee::create([
            'user_id'                 => $patient->id,
            'first_name'              => $patient->first_name,
            'last_name'               => $patient->last_name,
            'home_phone'              => formatPhoneNumberE164($patient->phoneNumbers->first()->number),
            'status'                  => $status,
            'care_ambassador_user_id' => $careAmbassadorId,
            'practice_id'             => $this->practice->id,
        ]);
        $enrollee->provider()->associate($provider);
        $enrollee->save();

        return $enrollee;
    }

    private function createPatientData(string $status)
    {
        $this->patient         = $this->createUserWithPatientCcmStatus($this->practice, $status);
        $this->patientEnrollee = $this->createEnrolleeWithStatus($this->patient, $this->careAmbassador->id, $status);
    }

    private function createPostmarkCallbackData(bool $requestToWithdraw, bool $nameIsSelf)
    {
        return  $this->postmarkRecord = PostmarkInboundMail::create(
            [
                'data' => json_encode(
                    [
                        'From'     => 'message.dispatch@callcenterusa.net',
                        'TextBody' => $this->getCallbackMailData($this->patient, $requestToWithdraw, $nameIsSelf),
                    ]
                ),
            ]
        );
    }

    private function createUserWithPatientCcmStatus(Practice $practice, string $status)
    {
        $user = $this->createUser($practice->id, 'participant', $status);
        $user->patientSummaries()->update([
            'no_of_successful_calls' => 0,
        ]);

        $user->patientSummaries->fresh();

        return $user;
//        $nurse = $this->createUser($practice->id, 'care-center');
//        app(NurseFinderEloquentRepository::class)->assign($user->id, $nurse->id);
    }

    private function generatePostmarkCallbackData(bool $requestToWithdraw, bool $nameIsSelf)
    {
        $this->postmarkRecord = $this->getCallbackMailData($this->patient, $requestToWithdraw, $nameIsSelf);
    }
}
