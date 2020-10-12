<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Postmark;

use App\PostmarkInboundCallback\InboundCallbackHelpers;
use App\ValueObjects\PostmarkCallback\MatchedDataPostmark;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class InboundCallbackMultimatchService
{
    public function multimatchResult(Collection $patientsMatched, string $reasoning)
    {
        return (new MatchedDataPostmark(
            $patientsMatched,
            $reasoning
        ))
            ->getMatchedData();
    }

    /**
     * @return string[]
     */
    public function parseNameFromCallerField(string $callerField)
    {
        $patientNameArray = $this->parsePostmarkInboundField($callerField);

        return [
            'firstName' => isset($patientNameArray[1]) ? $patientNameArray[1] : '',
            'lastName'  => isset($patientNameArray[2]) ? $patientNameArray[2] : '',
        ];
    }

    public function resolveSingleMatchResult(User $matchedPatient, array $inboundPostmarkData)
    {
        return app(InboundCallbackSingleMatchService::class)->singleMatchCallbackResult($matchedPatient, $inboundPostmarkData);
    }

    /**
     * @return array|Builder|void
     */
    public function tryToMatchByName(Collection $matchedWithPhone, array $inboundPostmarkData, int $recordId)
    {
        if (PostmarkInboundCallbackMatchResults::SELF === $inboundPostmarkData['Ptn']) {
            return $this->matchByCallerField($matchedWithPhone, $inboundPostmarkData, $recordId);
        }

        $matchedWithInboundName = $matchedWithPhone->where('display_name', '=', $inboundPostmarkData['Ptn']);
        
        if ($matchedWithInboundName->isEmpty() || 1 !== $matchedWithInboundName->count()) {
            return $this->multimatchResult($matchedWithPhone, PostmarkInboundCallbackMatchResults::MULTIPLE_PATIENT_MATCHES);
        }

        return $this->resolveSingleMatchResult($matchedWithInboundName->first(), $inboundPostmarkData);
    }

    /**
     * @return array
     */
    private function matchByCallerField(Collection $patientsMatchedByPhone, array $inboundPostmarkData, int $recordId)
    {
        $fullName  = $this->parseNameFromCallerField($inboundPostmarkData['Clr ID']);
        $firstName = $fullName['firstName'];
        $lastName  = $fullName['lastName'];

        $patientsMatchedByCallerFieldName = $patientsMatchedByPhone
            ->where('first_name', '=', $firstName)
            ->where('last_name', '=', $lastName);

        if (0 === $patientsMatchedByCallerFieldName->count()) {
            Log::critical("Couldn't match patient for record_id:$recordId in postmark_inbound_mail");
            sendSlackMessage('#carecoach_ops_alerts', "Could not find a patient match for record_id:[$recordId] in postmark_inbound_mail");

            return;
        }

        if (InboundCallbackHelpers::singleMatch($patientsMatchedByCallerFieldName)) {
            return $this->resolveSingleMatchResult($patientsMatchedByCallerFieldName->first(), $inboundPostmarkData);
        }

        return $this->multimatchResult($patientsMatchedByCallerFieldName, PostmarkInboundCallbackMatchResults::NO_NAME_MATCH_SELF);
    }

    /**
     * @return array|false|string[]
     */
    private function parsePostmarkInboundField(string $string)
    {
        return preg_split('/(?=[A-Z])/', preg_replace('/[^a-zA-Z]+/', '', $string));
    }
}
