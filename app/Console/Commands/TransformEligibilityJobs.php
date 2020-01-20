<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Loggers\NumberedAllergyFields;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Loggers\NumberedMedicationFields;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Loggers\NumberedProblemFields;
use Illuminate\Console\Command;

class TransformEligibilityJobs extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transform eligibility jobs';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ej:trans';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        EligibilityJob::orderBy('id')
            ->chunk(500, function ($jobs) {
                foreach ($jobs as $j) {
                    $j->data = $this->transformCsvRow($j->data);
                    $j->save();
                }
            });
    }

    private function transformCsvRow($patient)
    {
        if (count(preg_grep('/^problem_[\d]*/', array_keys($patient))) > 0) {
            $problems = (new NumberedProblemFields())->handle($patient);

            $patient['problems_string'] = json_encode([
                'Problems' => $problems,
            ]);
        }

        if (count(preg_grep('/^medication_[\d]*/', array_keys($patient))) > 0) {
            $medications = (new NumberedMedicationFields())->handle($patient);

            $patient['medications_string'] = json_encode([
                'Medications' => $medications,
            ]);
        }

        if (count(preg_grep('/^allergy_[\d]*/', array_keys($patient))) > 0) {
            $allergies = (new NumberedAllergyFields())->handle($patient);

            $patient['allergies_string'] = json_encode([
                'Allergies' => $allergies,
            ]);
        }

        return $patient;
    }
}
