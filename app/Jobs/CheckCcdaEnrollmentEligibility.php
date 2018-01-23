<?php

namespace App\Jobs;

use App\Importer\Loggers\Ccda\CcdToLogTranformer;
use App\Models\MedicalRecords\Ccda;
use App\Practice;
use App\Services\WelcomeCallListGenerator;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckCcdaEnrollmentEligibility implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $ccda;
    protected $practice;
    protected $transformer;
    private $filterLastEncounter;
    private $filterInsurance;
    private $filterProblems;

    /**
     * Create a new job instance.
     *
     * @param $ccda
     * @param Practice $practice
     * @param bool $filterLastEncounter
     * @param bool $filterInsurance
     * @param bool $filterProblems
     * @param bool $createEnrollees
     */
    public function __construct(
        $ccda,
        Practice $practice,
        $filterLastEncounter = null,
        $filterInsurance = null,
        $filterProblems = true
    ) {
        if (is_a($ccda, Ccda::class)) {
            $ccda = $ccda->id;
        }

        $this->ccda                = Ccda::find($ccda);
        $this->transformer         = new CcdToLogTranformer();
        $this->practice            = $practice;
        $this->filterLastEncounter = $filterLastEncounter;
        $this->filterInsurance     = $filterInsurance;
        $this->filterProblems      = $filterProblems;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->ccda->status != Ccda::DETERMINE_ENROLLEMENT_ELIGIBILITY) {
            return;
        }

        $this->determineEligibility();
    }

    private function determineEligibility()
    {
        $json = $this->ccda->bluebuttonJson();

        $demographics = collect($this->transformer->demographics($json->demographics));
        $problems     = collect($json->problems)->map(function ($prob) {
            $problem = array_merge($this->transformer->problem($prob));

            $codes = collect($this->transformer->problemCodes($prob))->sortByDesc(function ($code) {
                return $code['code'];
            })->values();

            foreach ($codes as $code) {
                if ($code['code']) {
                    return $code['code'];
                } elseif ($problem['name']) {
                    return $problem['name'];
                } elseif ($code['name']) {
                    return $code['name'];
                }
            }

            return '';
        });
        $insurance    = collect($json->payers)->map(function ($payer) {
            if (empty($payer->insurance)) {
                return false;
            }

            return implode($this->transformer->insurance($payer), ' - ');
        })->filter();

        $patient = $demographics->put('referring_provider_name', '');

        if ((is_null($this->filterLastEncounter) || $this->filterLastEncounter) && isset($json->encounters) && array_key_exists(0,
                $json->encounters) && isset($json->encounters[0]->date)) {
            $lastEncounter = $json->encounters[0]->date;

            if ($lastEncounter) {
                $this->filterLastEncounter = true;
                $patient->put('last_encounter', Carbon::parse($lastEncounter));
            } else {
                $this->filterLastEncounter = false;
            }
        }

        if (array_key_exists(0, $json->document->documentation_of)) {
            $provider = $this->transformer->provider($json->document->documentation_of[0]);
            $patient  = $patient->put('referring_provider_name', "{$provider['first_name']} {$provider['last_name']}");
        }

        $patient = $patient->put('problems', $problems);


        if ((is_null($this->filterInsurance) || $this->filterInsurance) && $insurance->isNotEmpty()) {
            $patient = $patient->put('primary_insurance', $insurance[0] ?? '');
            $patient = $patient->put('secondary_insurance', $insurance[1] ?? '');
        }

        $list = (new WelcomeCallListGenerator(
            collect([$patient]),
            $this->filterLastEncounter,
            $this->filterInsurance,
            $this->filterProblems,
            true,
            $this->practice,
            Ccda::class,
            $this->ccda->id
        ));

        $this->ccda->status = Ccda::ELIGIBLE;

        if ($list->patientList->isEmpty()) {
            $this->ccda->status = Ccda::INELIGIBLE;
        }

        $this->ccda->practice_id = $this->practice->id;
        $this->ccda->save();

        return $this->ccda->status;
    }
}
