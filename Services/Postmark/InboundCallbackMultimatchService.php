<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Services\Postmark;

use CircleLinkHealth\Customer\DTO\PostmarkCallbackInboundData;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Services\Postmark\PostmarkInboundCallbackMatchResults;
use CircleLinkHealth\SharedModels\DTO\InboundCallbackNameFields;
use CircleLinkHealth\SharedModels\Entities\PostmarkMultipleMatchData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class InboundCallbackMultimatchService
{
    public function multimatchResult(Collection $patientsMatched, string $reasoning)
    {
        return new PostmarkMultipleMatchData(
            $patientsMatched,
            $reasoning
        );
    }

    public function resolveSingleMatchResult(User $matchedPatient, PostmarkCallbackInboundData $inboundPostmarkData)
    {
        return app(InboundCallbackSingleMatchService::class)->singleMatchCallbackResult($matchedPatient, $inboundPostmarkData);
    }

    /**
     * @return array|\CircleLinkHealth\SharedModels\Entities\PostmarkSingleMatchData|PostmarkMultipleMatchData|void
     */
    public function tryToMatchByName(Collection $matchedWithPhone, PostmarkCallbackInboundData $inboundPostmarkData, int $recordId)
    {
        $patientFieldName    = $this->sanitizedPatientFieldName($inboundPostmarkData->get('ptn'));
        $callerIdFieldName   = $this->sanitizedPatientFieldName($inboundPostmarkData->get('callerId'));
        $fromFieldName       = $this->sanitizedPatientFieldName($inboundPostmarkData->get('from'));
        $nameFieldsToCompare = (new InboundCallbackNameFields($callerIdFieldName, $fromFieldName, $patientFieldName));

        if (PostmarkInboundCallbackMatchResults::SELF === $inboundPostmarkData->get('ptn')) {
            return $this->matchUsingNameFields($matchedWithPhone, $inboundPostmarkData, $recordId, $nameFieldsToCompare);
        }

        $matchedInboundPtnFieldName = $this->getMatchedPatientsUsingName($matchedWithPhone, $nameFieldsToCompare);

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
     * @return \Collection|Collection
     */
    private function getMatchedPatientsUsingName(Collection $matchedWithPhone, InboundCallbackNameFields $namesToCompare)
    {
        $matchedMatientsResults = collect();
        $matchedWithPhone->each(function ($patientUser) use (&$matchedMatientsResults, $namesToCompare) {
            $dbPatientName = $this->sanitizedPatientFieldName($patientUser->display_name);
            foreach ($namesToCompare->allNameFields() as $fieldToCompare) {
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
     * @return array|void
     */
    private function matchUsingNameFields(Collection $patientsMatchedByPhone, PostmarkCallbackInboundData $inboundPostmarkData, int $recordId, InboundCallbackNameFields $namesToCompare)
    {
        $patientsMatchedByNameFields = $this->getMatchedPatientsUsingName($patientsMatchedByPhone, $namesToCompare);

        if (0 === $patientsMatchedByNameFields->count()) {
            Log::critical("Couldn't match patient for record_id:$recordId in postmark_inbound_mail");
            sendSlackMessage('#carecoach_ops_alerts', "Could not find a patient match for record_id:[$recordId] in postmark_inbound_mail");

            return;
        }

        if (1 === $patientsMatchedByNameFields->count()) {
            return $this->resolveSingleMatchResult($patientsMatchedByNameFields->first(), $inboundPostmarkData);
        }

        return $this->multimatchResult($patientsMatchedByNameFields, PostmarkInboundCallbackMatchResults::NO_NAME_MATCH_SELF);
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
