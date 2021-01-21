<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Adapters;

use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\Entities\EligibilityBatch;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\Eligibility\Entities\Problem;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Loggers\CcdToLogTranformer;
use Illuminate\Support\Collection;

trait CreatesEligibilityJobFromObject
{
    protected $ccdaToLogTransformer;

    private function adaptInsurance($patient, Collection $insurance)
    {
        $patient = $patient->put('primary_insurance', '');
        $patient = $patient->put('secondary_insurance', '');
        $patient = $patient->put('tertiary_insurance', '');

        if ($insurance->isNotEmpty()) {
            $patient = $patient->put('primary_insurance', $insurance[0] ?? '');
            $patient = $patient->put('secondary_insurance', $insurance[1] ?? '');
            $patient = $patient->put('tertiary_insurance', $insurance[2] ?? '');
        }

        return $patient;
    }

    private function createEligibilityJob($patient, EligibilityBatch $batch, Practice $practice): ?EligibilityJob
    {
        $mrn = $patient['mrn_number'] ?? $patient['mrn'] ?? '';

        $hash = $practice->name.$patient['first_name'].$patient['last_name'].$mrn.$patient['city'].$patient['state'].$patient['zip'];

        return EligibilityJob::firstOrCreate(
            [
                'batch_id' => $batch->id,
                'hash'     => $hash,
            ],
            [
                'data' => $patient,
            ]
        );
    }

    private function createFromBlueButtonObject(object $decodedCcda, EligibilityBatch $batch, Practice $practice)
    {
        $demographics = collect($this->getCcdaTransformer()->demographics($decodedCcda->demographics));
        $problems     = collect($decodedCcda->problems)->map(
            function ($prob) {
                $problem = array_merge($this->getCcdaTransformer()->problem($prob));

                $code = collect($this->getCcdaTransformer()->problemCodes($prob))->filter(function ($code) {
                    if ( ! $code['code']) {
                        return false;
                    }

                    //LOINC is a codesystem that tells us what this element is (eg Problem, Medication, etc)
                    if ('LOINC' === strtoupper($code['code_system_name'])) {
                        return false;
                    }

                    //Ignore if OID says it's LOINC
                    if ('2.16.840.1.113883.6.1' === $code['code_system_oid']) {
                        return false;
                    }

                    return true;
                })->values()->first() ?? ['name' => null, 'code' => null, 'code_system_name' => null];

                return Problem::create(
                    [
                        'name'             => $problem['name'] ?? $code['name'],
                        'code'             => $code['code'],
                        'code_system_name' => $code['code_system_name'],
                    ]
                );
            }
        );

        $patient = $demographics->put('referring_provider_name', '');

        if ( ! $patient->get('mrn', null) && ! $patient->get('mrn_number', null)) {
            $patient = $patient->put('mrn', $decodedCcda->demographics->mrn_number);
        }

        $patient = $this->handleLastEncounter($patient, $decodedCcda, $batch);

        $provider = collect($this->getCcdaTransformer()->parseProviders($decodedCcda->document, $decodedCcda->demographics))
            ->transform(
                function ($p) {
                    $p = $this->getCcdaTransformer()->provider($p);
                    if ( ! $p['first_name'] && ! $p['last_name']) {
                        return false;
                    }

                    return $p;
                }
            )
            ->filter()
            ->values()
            ->first();

        if (is_array($provider) && array_key_exists('first_name', $provider) && array_key_exists(
            'last_name',
            $provider
        )) {
            $providerFullName = "{$provider['first_name']} {$provider['last_name']}";
            $patient          = $patient->put('referring_provider_name', $providerFullName);
        } else {
            $providerFullName = '';
            $patient          = $patient->put('referring_provider_name', $providerFullName);
        }

        $patient = $patient->put('problems', $problems);

        $patient = $this->adaptInsurance($patient, $this->getInsurance($decodedCcda));

        return $this->createEligibilityJob($patient, $batch, $practice);
    }

    private function getCcdaTransformer()
    {
        if ( ! $this->ccdaToLogTransformer) {
            $this->ccdaToLogTransformer = new CcdToLogTranformer();
        }

        return $this->ccdaToLogTransformer;
    }

    private function getInsurance($decodedCcda): Collection
    {
        return collect($decodedCcda->payers)->map(
            function ($payer) {
                if (empty($payer->insurance)) {
                    return false;
                }

                return implode(' - ', $this->getCcdaTransformer()->insurance($payer));
            }
        )->filter();
    }

    private function handleLastEncounter($patient, $parsedCcdObj, EligibilityBatch $batch)
    {
        $lastEncounter = false;
        $patient->put('last_encounter', '');

        $encounters = collect($parsedCcdObj->encounters);

        $lastEncounter = $encounters->sortByDesc(function ($el) {
            return $el->date;
        })->first();

        if (is_object($lastEncounter) && property_exists($lastEncounter, 'date')) {
            $v = \Validator::make(['date' => $lastEncounter->date], ['date' => 'required|date']);

            if ($v->passes()) {
                $patient['last_encounter'] = $lastEncounter->date;
            }
        }

        $check = $batch->shouldFilterLastEncounter();

        if ((is_null($check) || $check)) {
            $this->filterLastEncounter = (bool) $lastEncounter;
        }

        return $patient;
    }
}
