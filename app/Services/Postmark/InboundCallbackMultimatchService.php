<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Postmark;

use App\ValueObjects\PostmarkCallback\InboundCallbackNameFields;
use App\ValueObjects\PostmarkCallback\PostmarkMultipleMatchData;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class InboundCallbackMultimatchService
{
    public function multimatchResult(Collection $patientsMatched, string $reasoning)
    {
        return (new PostmarkMultipleMatchData(
            $patientsMatched,
            $reasoning
        ))
            ->getMatchedData();
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
        $patientFieldName    = $this->sanitizedPatientFieldName($inboundPostmarkData['ptn']);
        $callerIdFieldName   = $this->sanitizedPatientFieldName($inboundPostmarkData['callerId']);
        $fromFieldName       = $this->sanitizedPatientFieldName($inboundPostmarkData['from']);
        $nameFieldsToCompare = (new InboundCallbackNameFields($callerIdFieldName, $fromFieldName, $patientFieldName))->allNameFields();

        if (PostmarkInboundCallbackMatchResults::SELF === $inboundPostmarkData['ptn']) {
            return $this->matchUsingNameFields($matchedWithPhone, $inboundPostmarkData, $recordId, $nameFieldsToCompare);
        }

        $matchedInboundPtnFieldName = $this->getMatchedPatientsUsingName($matchedWithPhone, [$patientFieldName, $callerIdFieldName]);

        if (is_null($matchedInboundPtnFieldName)) {
            Log::critical("Couldn't match sanitized patient name for record_id:$recordId in postmark_inbound_mail");
            sendSlackMessage('#carecoach_ops_alerts', "Could not find a patient sanitized name match for record_id:[$recordId] in postmark_inbound_mail");

            return;
        }

        if ($matchedInboundPtnFieldName->isEmpty() || 1 !== $matchedInboundPtnFieldName->count()) {
            return $this->multimatchResult($matchedWithPhone, PostmarkInboundCallbackMatchResults::MULTIPLE_PATIENT_MATCHES);
        }

        return $this->resolveSingleMatchResult($matchedInboundPtnFieldName->first(), $inboundPostmarkData);
    }

    /**
     * @return Collection
     */
    private function getMatchedPatientsUsingName(Collection $matchedWithPhone, array $namesToCompare)
    {
        $matchedMatientsResults = collect();
        $matchedWithPhone->transform(function ($patientUser) use (&$matchedMatientsResults, $namesToCompare) {
            $dbPatientName = $this->sanitizedPatientFieldName($patientUser->display_name);
            foreach ($namesToCompare as $fieldToCompare) {
                if ($fieldToCompare === $dbPatientName) {
                    if ( ! in_array($patientUser->id, $matchedMatientsResults->pluck('id')->toArray())) {
                        $matchedMatientsResults->push($patientUser);
                    }
                }
            }
        });

        return $matchedMatientsResults;
    }
    
    /**
     * @param Collection $patientsMatchedByPhone
     * @param array $inboundPostmarkData
     * @param int $recordId
     * @param array $namesToCompare
     * @return array|void
     */
    private function matchUsingNameFields(Collection $patientsMatchedByPhone, array $inboundPostmarkData, int $recordId, array $namesToCompare)
    {
        $patientsMatchedByCallerFieldName = $this->getMatchedPatientsUsingName($patientsMatchedByPhone, $namesToCompare);

        if (0 === $patientsMatchedByCallerFieldName->count()) {
            Log::critical("Couldn't match patient for record_id:$recordId in postmark_inbound_mail");
            sendSlackMessage('#carecoach_ops_alerts', "Could not find a patient match for record_id:[$recordId] in postmark_inbound_mail");

            return;
        }

        if (1 === $patientsMatchedByCallerFieldName->count()) {
            return $this->resolveSingleMatchResult($patientsMatchedByCallerFieldName->first(), $inboundPostmarkData);
        }

        return $this->multimatchResult($patientsMatchedByCallerFieldName, PostmarkInboundCallbackMatchResults::NO_NAME_MATCH_SELF);
    }

    /**
     * @return string
     */
    private function sanitizedPatientFieldName(string $name)
    {
        $patientName      = trim(preg_replace('/[^A-Za-z ]/', '', strtolower($name)));
        $patientNameSplit = str_split(implode('', array_unique(explode(' ', $patientName))));
        sort($patientNameSplit);

        return implode('', $patientNameSplit);
    }
}
