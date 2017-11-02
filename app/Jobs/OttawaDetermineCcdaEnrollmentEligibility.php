<?php

namespace App\Jobs;

use App\CLH\Repositories\CCDImporterRepository;
use App\Importer\Loggers\Ccda\CcdToLogTranformer;
use App\Models\MedicalRecords\Ccda;
use App\Practice;
use App\Services\WelcomeCallListGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OttawaDetermineCcdaEnrollmentEligibility implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;
    protected $ccda;
    protected $transformer;

    /**
     * Create a new job instance.
     *
     * @param Ccda $ccda
     */
    public function __construct(Ccda $ccda)
    {
        $this->ccda = Ccda::find($ccda->id);
        $this->transformer = new CcdToLogTranformer();
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

        $json = json_decode((new CCDImporterRepository())->toJson($this->ccda->xml));

        $demographics = collect($this->transformer->demographics($json->demographics));
        $problems = collect($json->problems)->map(function ($prob) {
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
        $insurance = collect($json->payers)->map(function ($payer) {
            if (empty($payer->insurance)) {
                return false;
            }

            return implode($this->transformer->insurance($payer), ' - ');
        })->filter();

        $patient = $demographics->put('referring_provider_name', '');

        if (array_key_exists(0, $json->document->documentation_of)) {
            $provider = $this->transformer->provider($json->document->documentation_of[0]);
            $patient = $patient->put('referring_provider_name', "{$provider['first_name']} {$provider['last_name']}");
        }

        $patient = $patient->put('problems', $problems);


        $filterInsurance = false;

        if (!$insurance->isEmpty()) {
            $patient = $patient->put('primary_insurance', $insurance[0] ?? '');
            $patient = $patient->put('secondary_insurance', $insurance[1] ?? '');

//            $filterInsurance = true;
        }


        $practice = Practice::whereName('ottawa-family-physicians')
            ->first();

        $list = (new WelcomeCallListGenerator(
            collect([$patient]),
            false,
            $filterInsurance,
            true,
            true,
            $practice,
            Ccda::class,
            $this->ccda->id
        ));

        $this->ccda->status = Ccda::ELIGIBLE;

        if ($list->patientList->isEmpty()) {
            $this->ccda->status = Ccda::INELIGIBLE;
        }

        $this->ccda->practice_id = $practice->id;
        $this->ccda->save();
    }
}
