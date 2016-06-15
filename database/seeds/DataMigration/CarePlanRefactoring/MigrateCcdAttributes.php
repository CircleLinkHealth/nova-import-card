<?php

class MigrateCcdAttributes extends \Illuminate\Database\Seeder
{
    public function run()
    {
        //ALLERGIES

        $this->command->comment(PHP_EOL . 'Begin Migrating Allergies...' . PHP_EOL);

        $allergy_imports = \App\CLH\CCD\ImportedItems\AllergyImport::all();

        foreach ($allergy_imports as $allergy_import) {
            $patient_id = \App\Models\CCD\Ccda::where('id', $allergy_import->ccda_id)->lists('patient_id')->first();

            if (is_null($patient_id)) {
                $this->command->info("No user associated to CCDA for MedicationsImport ID" . $allergy_import->id);
                continue;
            }

            $result = \App\Models\CCD\CcdAllergy::firstOrCreate([
                'ccda_id' => $allergy_import->ccda_id,
                'vendor_id' => $allergy_import->vendor_id,
                'allergen_name' => $allergy_import->allergen_name,
                'ccd_allergy_log_id' => $allergy_import->ccd_allergy_log_id,
                'patient_id' => $patient_id,
            ]);

            $this->command->info("Migrated AllergyImport ID: " . $allergy_import->id . " to CcdAllergy ID: " . $result->id);
        }

        $this->command->comment(PHP_EOL . 'ALLERGIES MIGRATED' . PHP_EOL);

        //MEDICATIONS
        $this->command->comment(PHP_EOL . 'Begin Migrating Medications...' . PHP_EOL);
        $medications_imports = \App\CLH\CCD\ImportedItems\MedicationImport::all();

        foreach ($medications_imports as $medications_import) {
            $patient_id = \App\Models\CCD\Ccda::where('id', $medications_import->ccda_id)->lists('patient_id')->first();

            if (is_null($patient_id)) {
                $this->command->info("No user associated to CCDA for MedicationsImport ID: " . $medications_import->id);
                continue;
            }

            $result = \App\Models\CCD\CcdMedication::firstOrCreate([
                'ccda_id' => $medications_import->ccda_id,
                'vendor_id' => $medications_import->vendor_id,
                'ccd_medication_log_id' => $medications_import->ccd_medication_log_id,
                'medication_group_id' => $medications_import->medication_group_id,
                'name' => $medications_import->name,
                'sig' => $medications_import->sig,
                'code' => $medications_import->code,
                'code_system' => $medications_import->code_system,
                'code_system_name' => $medications_import->code_system_name,
                'patient_id' => $patient_id,
            ]);

            $this->command->info("Migrated MedicationsImport ID: " . $medications_import->id . " to CcdMedication ID: " . $result->id);
        }

        $this->command->comment(PHP_EOL . 'MEDICATIONS MIGRATED' . PHP_EOL);

        //PROBLEMS [https://www.youtube.com/watch?v=LloIp0HMJjc]

        $problems_imports = \App\CLH\CCD\ImportedItems\ProblemImport::all();
        $this->command->comment(PHP_EOL . 'Begin Migrating Problems...' . PHP_EOL);

        foreach ($problems_imports as $problems_import) {
            $ccda = \App\Models\CCD\Ccda::where('id', $problems_import->ccda_id)->first();

            if (empty($ccda)) {
                $this->command->info("No user associated to CCDA for ProblemsImport ID: " . $problems_import->id);
                continue;
            }

            $patient_id = $ccda->patient_id;

            if (empty($patient_id)) {
                $this->command->info("No user associated to CCDA for ProblemsImport ID: " . $problems_import->id);
                continue;
            }

            try {
                $result = \App\Models\CCD\CcdProblem::firstOrCreate([
                    'ccda_id' => $problems_import->ccda_id,
                    'vendor_id' => $problems_import->vendor_id,
                    'ccd_problem_log_id' => $problems_import->ccd_problem_log_id,
                    'name' => $problems_import->name,
                    'activate' => $problems_import->activate,
                    'cpm_problem_id' => $problems_import->cpm_problem_id,
                    'code_system_name' => $problems_import->code_system_name,
                    'code' => $problems_import->code,
                    'code_system' => $problems_import->code_system,

                    'patient_id' => $patient_id,
                ]);
            } catch (\Exception $e)
            {
                //If it is failing because of a foreign key constraint, then enter it without cpm_problem_id
                if ($e->errorInfo[1] == 1452)
                {
                    $result = \App\Models\CCD\CcdProblem::firstOrCreate([
                        'ccda_id' => $problems_import->ccda_id,
                        'vendor_id' => $problems_import->vendor_id,
                        'ccd_problem_log_id' => $problems_import->ccd_problem_log_id,
                        'name' => $problems_import->name,
                        'activate' => $problems_import->activate,
                        'code_system_name' => $problems_import->code_system_name,
                        'code' => $problems_import->code,
                        'code_system' => $problems_import->code_system,

                        'patient_id' => $patient_id,
                    ]);
                }
            }


            $this->command->info("Migrated ProblemsImport ID: " . $problems_import->id . " to CcdProblems ID: " . $result->id);
        }

        $this->command->comment(PHP_EOL . 'PROBLEMS MIGRATED' . PHP_EOL);
    }
}