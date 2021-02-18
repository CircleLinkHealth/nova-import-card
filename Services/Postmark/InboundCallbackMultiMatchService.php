<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Services\Postmark;

use CircleLinkHealth\Customer\DTO\PostmarkCallbackInboundData;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\DTO\InboundCallbackNameFields;
use CircleLinkHealth\SharedModels\Entities\PostmarkMatchedData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class InboundCallbackMultiMatchService
{
    public function matchByName(Collection $matchedWithPhone, PostmarkCallbackInboundData $inboundPostmarkData, int $recordId): ?PostmarkMatchedData
    {
        $patientFieldName    = $this->sanitizedPatientFieldName($inboundPostmarkData->get('ptn'));
        $callerIdFieldName   = $this->sanitizedPatientFieldName($inboundPostmarkData->get('callerId'));
        $fromFieldName       = $this->sanitizedPatientFieldName($inboundPostmarkData->get('from'));
        $nameFieldsToCompare = (new InboundCallbackNameFields($callerIdFieldName, $fromFieldName, $patientFieldName));

        $matchedInboundPtnFieldName = $this->getMatchedPatientsUsingName($matchedWithPhone, $nameFieldsToCompare);
        $count                      = $matchedInboundPtnFieldName->count();

        if (0 === $count) {
            Log::critical("Couldn't match sanitized patient name for record_id:$recordId in postmark_inbound_mail");
            sendSlackMessage('#carecoach_ops_alerts', "Could not find a patient sanitized name match for record_id:[$recordId] in postmark_inbound_mail");

            return null;
        }

        if (1 === $count) {
            return app(InboundCallbackSingleMatchService::class)
                ->getSingleMatchCallbackResult($matchedInboundPtnFieldName->first(), $inboundPostmarkData);
        }

        $reasoning = PostmarkInboundCallbackMatchResults::SELF === $inboundPostmarkData->get('ptn')
            ? PostmarkInboundCallbackMatchResults::NO_NAME_MATCH_SELF
            : PostmarkInboundCallbackMatchResults::MULTIPLE_PATIENT_MATCHES;

        return new PostmarkMatchedData($matchedWithPhone->toArray(), $reasoning);
    }

    private function getMatchedPatientsUsingName(Collection $matchedWithPhone, InboundCallbackNameFields $namesToCompare): Collection
    {
        $matchedPatientsResults = collect();
        $matchedWithPhone->each(function (User $patientUser) use (&$matchedPatientsResults, $namesToCompare) {
            $foundMatch = false;
            $dbPatientName = $this->getPatientNameFromDb($patientUser);
            foreach ($namesToCompare->allNameFields() as $fieldToCompare) {
                if ($fieldToCompare === $dbPatientName) {
                    $foundMatch = true;
                    break;
                }
            }
            if ($foundMatch) {
                $matchedPatientsResults->push($patientUser);
            }
        });

        return $matchedPatientsResults;
    }

    private function getPatientNameFromDb(User $patient): string
    {
        if (0 === $patient->id) {
            $name = $patient->enrollee->first_name.' '.$patient->enrollee->last_name;
        } else {
            $name = $patient->display_name;
        }

        return $this->sanitizedPatientFieldName($name);
    }

    private function sanitizedPatientFieldName(string $name): string
    {
        $patientName      = trim(preg_replace('/[^A-Za-z ]/', '', strtolower($name)));
        $patientNameSplit = str_split(implode('', array_unique(explode(' ', $patientName))));
        sort($patientNameSplit);

        return implode('', $patientNameSplit);
    }
}
