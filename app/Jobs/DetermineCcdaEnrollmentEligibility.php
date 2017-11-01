<?php

namespace App\Jobs;

use App\CLH\Repositories\CCDImporterRepository;
use App\Importer\Loggers\Ccda\CcdToLogTranformer;
use App\Models\MedicalRecords\Ccda;
use App\Models\PatientData\LGH\LGHInsurance;
use App\Practice;
use App\Services\WelcomeCallListGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DetermineCcdaEnrollmentEligibility implements ShouldQueue
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
            $problem = $this->transformer->problem($prob);

            if ($problem['code']) {
                return $problem['code'];
            } elseif ($problem['translation_code']) {
                return $problem['translation_code'];
            } elseif ($problem['name']) {
                return $problem['name'];
            } elseif ($problem['translation_name']) {
                return $problem['translation_name'];
            }

            return '';
        });

        $patient = $demographics->put('referring_provider_name', '');

        if (array_key_exists(0, $json->document->documentation_of)) {
            $provider = $this->transformer->provider($json->document->documentation_of[0]);
            $patient = $patient->put('referring_provider_name', "{$provider['first_name']} {$provider['last_name']}");
        }

        $patient = $patient->put('problems', $problems);

        $insurance = LGHInsurance::where('mrn', $this->ccda->mrn)->first();

        $filterInsurance = false;

        if ($insurance) {
            $patient = $patient->put('primary_insurance', $insurance->getAttributes()['PRIMARY INSURANCE']);
            $patient = $patient->put('secondary_insurance', $insurance->getAttributes()['SECONDARY INSURANCE']);

            $filterInsurance = true;
        }


        $lgh = Practice::whereName('lafayette-general-health')
            ->first();

        $list = (new WelcomeCallListGenerator(
            collect([$patient]),
            false,
            $filterInsurance,
            true,
            true,
            $lgh,
            Ccda::class,
            $this->ccda->id
        ));

        $this->ccda->status = Ccda::ELIGIBLE;

        if ($list->patientList->isEmpty()) {
            $this->ccda->status = Ccda::INELIGIBLE;
        }

        $this->ccda->practice_id = $lgh->id;
        $this->ccda->save();
    }
}
