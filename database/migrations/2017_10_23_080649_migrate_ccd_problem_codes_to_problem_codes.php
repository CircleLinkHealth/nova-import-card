<?php

use App\Models\CCD\Problem;
use App\Models\ProblemCode;
use Illuminate\Database\Migrations\Migration;

class MigrateCcdProblemCodesToProblemCodes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $problems = Problem::whereNotNull('code')->get()->map(function ($p) {
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
            ], [
                'is_imported' => true,
            ]);
        });
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
