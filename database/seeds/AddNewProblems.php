<?php

use App\CarePlanTemplate;
use App\CLH\CCD\Importer\SnomedToCpmIcdMap;
use App\Models\CPM\CpmProblem;
use Illuminate\Database\Seeder;

class AddNewProblems extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $defaultCarePlan = getDefaultCarePlanTemplate();

        foreach ($this->problems() as $name => $codes) {
            //Does a CPMProblem exist?
            $cpmProblem = CpmProblem::firstOrCreate(['name' => $name]);

            if (!in_array($cpmProblem->id, $defaultCarePlan->cpmProblems->pluck('id')->all())) {
                $defaultCarePlan->cpmProblems()->attach($cpmProblem, [
                    'has_instruction' => true,
                    'page'            => 1,
                ]);
            }

            //ICD9 Check
            foreach ($codes['icd9'] as $icd9) {
                $map = SnomedToCpmIcdMap::updateOrCreate([
                    'icd_9_code' => $icd9,
                ], [
                    'cpm_problem_id' => $cpmProblem->id,
                    'icd_9_name'     => $cpmProblem->name,
                ]);
            }

            //ICD10 Check
            foreach ($codes['icd10'] as $icd10) {
                $map = SnomedToCpmIcdMap::updateOrCreate([
                    'icd_10_code' => $icd10,
                ], [
                    'cpm_problem_id' => $cpmProblem->id,
                    'icd_10_name'    => $cpmProblem->name,
                ]);
            }
        }
    }

    /**
     * The array of problems to be added
     *
     * @return array
     */
    public function problems() : array
    {
        $problems = [];
        //Template
        //Copy the below template to add new problems
//        $problems['Problem Name Here'] = [
//            'icd9'  => [
//
//            ],
//            'icd10' => [
//
//            ],
//        ];


        return $problems;
    }
}
