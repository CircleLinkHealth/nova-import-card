<?php

namespace App\Jobs;

use App\Importer\Loggers\Ccda\CcdToLogTranformer;
use App\Models\MedicalRecords\Ccda;
use App\Practice;
use App\Services\WelcomeCallListGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckCcdaEnrollmentEligibility implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;
    protected $ccda;
    protected $practice;
    protected $transformer;

    /**
     * Create a new job instance.
     *
     * @param Ccda $ccda
     * @param Practice $practice
     */
    public function __construct(Ccda $ccda, Practice $practice)
    {
        $this->ccda        = Ccda::find($ccda->id);
        $this->transformer = new CcdToLogTranformer();
        $this->practice    = $practice;
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

        $filterLastEncounter = false;
        if (isset($json->encounters) && array_key_exists(0, $json->encounters) && isset($json->encounters[0]->date)) {
            $lastEncounter = $json->encounters[0]->date;

            if ($lastEncounter) {
                $filterLastEncounter = true;
                $patient->put('last_encounter', $lastEncounter);
            }
        }

        if (array_key_exists(0, $json->document->documentation_of)) {
            $provider = $this->transformer->provider($json->document->documentation_of[0]);
            $patient  = $patient->put('referring_provider_name', "{$provider['first_name']} {$provider['last_name']}");
        }

        $patient = $patient->put('problems', $problems);


        $filterInsurance = false;

        if ($insurance->isNotEmpty()) {
            $patient = $patient->put('primary_insurance', $insurance[0] ?? '');
            $patient = $patient->put('secondary_insurance', $insurance[1] ?? '');

//            $filterInsurance = true;
        }

        $list = (new WelcomeCallListGenerator(
            collect([$patient]),
            $filterLastEncounter,
            $filterInsurance,
            true,
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
