<?php

use App\Models\CCD\Problem;
use App\Models\ProblemCode;
use Illuminate\Database\Seeder;

class MigrateCcdProblemCodesToProblemCodesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $problems = Problem::select([
            'id',
            'code_system_name',
            'code_system',
            'code',
        ])
            ->whereNotNull('code')
            ->get();


        foreach ($problems as $p) {
            if (!$p->code_system_name) {
                if (!$p->code_system && !$this->getCodeSystemName($p)) {
                    return;
                }

                $p->code_system_name = $this->getCodeSystemName($p);
            }

            ProblemCode::updateOrCreate([
                'problem_id'       => $p->id,
                'code_system_name' => $p->code_system_name,
                'code_system_oid'  => $p->code_system,
                'code'             => $p->code,
            ]);
        }
    }

    public function getCodeSystemName(Problem $problem)
    {
        if ($problem->code_system == '2.16.840.1.113883.6.96'
            || str_contains(strtolower($problem->code_system_name), ['snomed'])) {
            return 'SNOMED CT';
        }

        if ($problem->code_system == '2.16.840.1.113883.6.103'
            || str_contains(strtolower($problem->code_system_name), ['9'])) {
            return 'ICD-9';
        }

        if ($problem->code_system == '2.16.840.1.113883.6.3'
            || str_contains(strtolower($problem->code_system_name), ['10'])) {
            return 'ICD-10';
        }

        return false;
    }
}
