<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\SharedModels\Entities\Problem;
use CircleLinkHealth\SharedModels\Entities\ProblemCode;
use Illuminate\Database\Seeder;

class MigrateCcdProblemCodesToProblemCodesSeeder extends Seeder
{
    public function getCodeSystemName(Problem $problem)
    {
        if ('2.16.840.1.113883.6.96' == $problem->code_system
            || str_contains(strtolower($problem->code_system_name), ['snomed'])) {
            return 'SNOMED CT';
        }

        if ('2.16.840.1.113883.6.103' == $problem->code_system
            || str_contains(strtolower($problem->code_system_name), ['9'])) {
            return 'ICD-9';
        }

        if ('2.16.840.1.113883.6.3' == $problem->code_system
            || str_contains(strtolower($problem->code_system_name), ['10'])) {
            return 'ICD-10';
        }

        return false;
    }

    /**
     * Run the database seeds.
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
            if ( ! $p->code_system_name) {
                if ( ! $p->code_system && ! $this->getCodeSystemName($p)) {
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
}
