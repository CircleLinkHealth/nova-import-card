<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Postmark;

use App\PostmarkInboundCallback\InboundCallbackHelpers;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Builder;

class PostmarkInboundCallbackMatchResults
{
    const CREATE_CALLBACK             = 'create_callback';
    const NO_NAME_MATCH               = 'no_name_match';
    const NO_NAME_MATCH_SELF          = 'no_name_match_self';
    const NOT_CONSENTED_CA_ASSIGNED   = 'non_consented_ca_assigned';
    const NOT_CONSENTED_CA_UNASSIGNED = 'non_consented_ca_unassigned';
    const NOT_ENROLLED                = 'not_enrolled';
    const QUEUED_AND_UNASSIGNED       = 'queue_auto_enrollment_and_unassigned';
    const SELF                        = 'SELF';
    const WITHDRAW_REQUEST            = 'withdraw_request';

    private array $postmarkCallbackData;
    private int $recordId;

    /**
     * PostmarkInboundCallbackMatchResults constructor.
     */
    public function __construct(array $postmarkCallbackData, int $recordId)
    {
        $this->postmarkCallbackData = $postmarkCallbackData;
        $this->recordId             = $recordId;
    }

    /**
     * @return array|Builder|void
     */
    public function matchedPatientsData()
    {
        /** @var Builder $postmarkInboundPatientsMatched */
        $postmarkInboundPatientsMatched = $this->tryToMatchByPhone($this->postmarkCallbackData);

        if (InboundCallbackHelpers::singleMatch($postmarkInboundPatientsMatched->get())) {
            /** @var User $matchedPatient */
            $matchedPatient = $postmarkInboundPatientsMatched->first();

            return app(InboundCallbackSingleMatchService::class)->singleMatchCallbackResult($matchedPatient, $this->postmarkCallbackData);
        }

        if (InboundCallbackHelpers::multiMatch($postmarkInboundPatientsMatched->get())) {
            return app(InboundCallbackMultimatchService::class)
                ->tryToMatchByName($postmarkInboundPatientsMatched->get(), $this->postmarkCallbackData, $this->recordId);
        }

        // I kept this last cause i think is an expnsive query, i think it will happen rarely.
        // This step is not required in the ticket. Evala to pou tin pougka.
        if ($postmarkInboundPatientsMatched->get()->isEmpty()) {
            $possibleMatchedData = $this->tryToMatchByNameFromDb($this->postmarkCallbackData);

            if (InboundCallbackHelpers::singleMatch($possibleMatchedData)) {
                return app(InboundCallbackSingleMatchService::class)
                    ->singleMatchCallbackResult($possibleMatchedData->first(), $this->postmarkCallbackData);
            }

            if (InboundCallbackHelpers::multiMatch($possibleMatchedData)) {
                return app(InboundCallbackMultimatchService::class)
                    ->multimatchResult($possibleMatchedData, 'need a reason name');
            }
        }
    }

    private function tryToMatchByNameFromDb(array $postmarkCallbackData)
    {
        $name = $postmarkCallbackData['Ptn'];

        $possibleMatches = collect();
        User::ofType('participant')
            ->chunk(100, function ($users) use (&$possibleMatches, $name) {
                $match = $users->where('display_name', $name)->first();
                if ( ! is_null($match)) {
                    $possibleMatches->push($match);
                }
            });

        return $possibleMatches;
    }

    /**
     * @return Builder|User
     */
    private function tryToMatchByPhone(array $inboundPostmarkData)
    {
        return User::ofType('participant')
            ->with('patientInfo', 'enrollee', 'phoneNumbers') //Get only what you need from each relationship mate.
            ->whereHas('phoneNumbers', function ($phoneNumber) use ($inboundPostmarkData) {
                $phoneNumber->where('number', $inboundPostmarkData['Phone']);
            });
    }
}
