<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Postmark;

use App\Traits\CallbackEligibilityMeter;
use App\ValueObjects\PostmarkCallback\MatchedDataPostmark;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class InboundCallbackMultimatchService
{
    use CallbackEligibilityMeter;

    /**
     * @return array|Builder|void
     */
    public function filterPostmarkInboundPatientsByName(Collection $patientsMatchedByPhone, array $inboundPostmarkData, int $recordId)
    {
        if (PostmarkInboundCallbackMatchResults::SELF === $inboundPostmarkData['Ptn']) {
            return $this->matchByCallerField($patientsMatchedByPhone, $inboundPostmarkData, $recordId);
        }

        $patientsMatchWithInboundName = $patientsMatchedByPhone->where('display_name', '=', $inboundPostmarkData['Ptn']);

        if ($patientsMatchWithInboundName->isEmpty() || 1 !== $patientsMatchWithInboundName->count()) {
            sendSlackMessage('#carecoach_ops_alerts', "Inbound callback with record id:$recordId was matched with phone but failed to match with user name.");

            return $this->multimatchResult($patientsMatchedByPhone, PostmarkInboundCallbackMatchResults::NO_NAME_MATCH);
        }

        return $this->resolvedSingleMatchResult($patientsMatchWithInboundName->first(), $inboundPostmarkData);
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

    public function resolvedSingleMatchResult(User $matchedPatient, array $inboundPostmarkData)
    {
        return app(InboundCallbackSingleMatchService::class)->singleMatchCallbackResult($matchedPatient, $inboundPostmarkData);
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

        if ($this->singleMatch($patientsMatchedByCallerFieldName)) {
            return $this->resolvedSingleMatchResult($patientsMatchedByCallerFieldName->first(), $inboundPostmarkData);
        }

        return $this->multimatchResult($patientsMatchedByCallerFieldName, PostmarkInboundCallbackMatchResults::NO_NAME_MATCH_SELF);
    }

    private function multimatchResult(Collection $patientsMatched, string $reasoning)
    {
        return (new MatchedDataPostmark(
            $patientsMatched,
            $reasoning
        ))
            ->getMatchedData();
    }

    /**
     * @return array|false|string[]
     */
    private function parsePostmarkInboundField(string $string)
    {
        return preg_split('/(?=[A-Z])/', preg_replace('/[^a-zA-Z]+/', '', $string));
    }
}
