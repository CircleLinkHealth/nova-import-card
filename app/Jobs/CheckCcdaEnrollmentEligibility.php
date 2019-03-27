<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\EligibilityBatch;
use App\EligibilityJob;
use App\Importer\Loggers\Ccda\CcdToLogTranformer;
use App\Models\MedicalRecords\Ccda;
use App\Practice;
use App\Services\Eligibility\Entities\Problem;
use App\Services\WelcomeCallListGenerator;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class CheckCcdaEnrollmentEligibility implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $ccda;
    protected $practice;
    protected $transformer;
    /**
     * @var EligibilityBatch
     */
    private $batch;
    private $filterInsurance;
    private $filterLastEncounter;
    private $filterProblems;

    /**
     * Create a new job instance.
     *
     * @param $ccda
     * @param Practice         $practice
     * @param EligibilityBatch $batch
     */
    public function __construct(
        $ccda,
        Practice $practice,
        EligibilityBatch $batch
    ) {
        if (is_a($ccda, Ccda::class)) {
            $ccda = $ccda->id;
        }

        $this->ccda                = Ccda::find($ccda);
        $this->transformer         = new CcdToLogTranformer();
        $this->practice            = $practice;
        $this->filterLastEncounter = (bool) $batch->options['filterLastEncounter'];
        $this->filterInsurance     = (bool) $batch->options['filterInsurance'];
        $this->filterProblems      = (bool) $batch->options['filterProblems'];
        $this->batch               = $batch;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        if (Ccda::DETERMINE_ENROLLEMENT_ELIGIBILITY != $this->ccda->status) {
            return;
        }

        $this->determineEligibility();
    }

    private function createEligibilityJob($patient)
    {
        $mrn = $patient['mrn_number'] ?? $patient['mrn'] ?? '';

        $hash = $this->practice->name.$patient['first_name'].$patient['last_name'].$mrn.$patient['city'].$patient['state'].$patient['zip'];

        return EligibilityJob::create(
            [
                'batch_id' => $this->batch->id,
                'hash'     => $hash,
                'data'     => $patient,
            ]
        );
    }

    private function determineEligibility()
    {
        $json = $this->ccda->bluebuttonJson();

        $demographics = collect($this->transformer->demographics($json->demographics));
        $problems     = collect($json->problems)->map(
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
        $insurance = collect($json->payers)->map(
            function ($payer) {
                if (empty($payer->insurance)) {
                    return false;
                }

                return implode($this->transformer->insurance($payer), ' - ');
            }
        )->filter();

        $patient = $demographics->put('referring_provider_name', '');

        if ( ! $patient->get('mrn', null) && ! $patient->get('mrn_number', null)) {
            $patient = $patient->put('mrn', $this->ccda->mrn);
        }

        $patient = $this->handleLastEncounter($patient, $json);

        $provider = collect($this->transformer->parseProviders($json->document, $json->demographics))
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

        $patient = $this->handleInsurance($patient, $insurance);

        $job = $this->createEligibilityJob($patient);

        $list = (new WelcomeCallListGenerator(
            collect([$patient]),
            $this->filterLastEncounter,
            $this->filterInsurance,
            $this->filterProblems,
            true,
            $this->practice,
            Ccda::class,
            $this->ccda->id,
            $this->batch,
            $job
        ));

        $this->ccda->status = Ccda::ELIGIBLE;

        if ($list->patientList->isEmpty()) {
            $this->ccda->status = Ccda::INELIGIBLE;
        }

        $this->ccda->referring_provider_name = $providerFullName;
        $this->ccda->practice_id             = $this->practice->id;
        $this->ccda->save();

        return $this->ccda->status;
    }

    private function handleInsurance($patient, Collection $insurance)
    {
        $patient = $patient->put('primary_insurance', '');
        $patient = $patient->put('secondary_insurance', '');
        $patient = $patient->put('tertiary_insurance', '');

        if ($insurance->isNotEmpty()) {
            $patient = $patient->put('primary_insurance', $insurance[0] ?? '');
            $patient = $patient->put('secondary_insurance', $insurance[1] ?? '');
            $patient = $patient->put('tertiary_insurance', $insurance[2] ?? '');
        }

        if (is_null($this->filterInsurance)) {
            $count = 0;

            foreach (
                [
                    $patient['primary_insurance'],
                    $patient['secondary_insurance'],
                    $patient['tertiary_insurance'],
                ] as $string
            ) {
                if ($string) {
                    ++$count;
                }
            }

            $this->filterInsurance = $count > 0;
        }

        return $patient;
    }

    private function handleLastEncounter($patient, $ccdaJson)
    {
        $lastEncounter = false;
        $patient->put('last_encounter', '');

        if (isset($ccdaJson->encounters)
            && array_key_exists(0, $ccdaJson->encounters)
            && isset($ccdaJson->encounters[0]->date)) {
            if ($ccdaJson->encounters[0]->date) {
                $lastEncounter = $ccdaJson->encounters[0]->date;
                $patient->put('last_encounter', Carbon::parse($lastEncounter));
            }
        }

        if ((is_null($this->filterLastEncounter) || $this->filterLastEncounter)) {
            $this->filterLastEncounter = (bool) $lastEncounter;
        }

        return $patient;
    }
}
