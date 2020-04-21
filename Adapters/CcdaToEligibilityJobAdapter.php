<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Adapters;

use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\Contracts\EligibilityCheckAdapter;
use CircleLinkHealth\Eligibility\Entities\EligibilityBatch;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\Eligibility\Entities\Problem;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\MedicalRecord;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Loggers\CcdToLogTranformer;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Support\Collection;

class CcdaToEligibilityJobAdapter implements EligibilityCheckAdapter
{
    /**
     * @var EligibilityBatch
     */
    protected $batch;
    /**
     * @var Practice
     */
    protected $practice;
    /**
     * @var \CircleLinkHealth\SharedModels\Entities\Ccda
     */
    private $ccda;

    /**
     * @var \stdClass
     */
    private $decodedCcda;

    /**
     * @var CcdToLogTranformer
     */
    private $transformer;

    public function __construct(Ccda $ccda, Practice $practice, EligibilityBatch $batch)
    {
        $this->ccda        = $ccda;
        $this->practice    = $practice;
        $this->batch       = $batch;
        $this->transformer = new CcdToLogTranformer();
    }

    /**
     * @throws \Exception
     */
    public function adaptToEligibilityJob(): EligibilityJob
    {
        $this->decodedCcda = optional($this->ccda->bluebuttonJson());

        $demographics = collect($this->transformer->demographics($this->decodedCcda->demographics));
        $problems     = collect($this->decodedCcda->problems)->map(
            function ($prob) {
                $problem = array_merge($this->transformer->problem($prob));

                $code = collect($this->transformer->problemCodes($prob))->sortByDesc(
                    function ($code) {
                        return empty($code['code'])
                                ? false
                                : $code['code'];
                    }
                )->filter()->values()->first() ?? ['name' => null, 'code' => null, 'code_system_name' => null];

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
            $patient = $patient->put('mrn', $this->ccda->mrn);
        }

        $patient = $this->handleLastEncounter($patient, $this->decodedCcda);

        $provider = collect($this->transformer->parseProviders($this->decodedCcda->document, $this->decodedCcda->demographics))
            ->transform(
                function ($p) {
                    $p = $this->transformer->provider($p);
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

        $patient = $this->adaptInsurance($patient, $this->getInsurance());

        $this->ccda->referring_provider_name = $providerFullName;
        $this->ccda->practice_id             = $this->practice->id;

        $patient['medical_record_type'] = Ccda::class;
        $patient['medical_record_id']   = $this->ccda->id;

        return $this->createEligibilityJob($patient);
    }

    public function getMedicalRecord(): MedicalRecord
    {
        return $this->ccda;
    }

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

    private function createEligibilityJob($patient): ?EligibilityJob
    {
        $mrn = $patient['mrn_number'] ?? $patient['mrn'] ?? '';

        $hash = $this->practice->name.$patient['first_name'].$patient['last_name'].$mrn.$patient['city'].$patient['state'].$patient['zip'];

        return EligibilityJob::firstOrCreate(
            [
                'batch_id' => $this->batch->id,
                'hash'     => $hash,
            ],
            [
                'data' => $patient,
            ]
        );
    }

    private function getInsurance(): Collection
    {
        return collect($this->decodedCcda->payers)->map(
            function ($payer) {
                if (empty($payer->insurance)) {
                    return false;
                }

                return implode($this->transformer->insurance($payer), ' - ');
            }
        )->filter();
    }

    private function handleLastEncounter($patient, $parsedCcdObj)
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

        $check = $this->batch->shouldFilterLastEncounter();

        if ((is_null($check) || $check)) {
            $this->filterLastEncounter = (bool) $lastEncounter;
        }

        return $patient;
    }
}
