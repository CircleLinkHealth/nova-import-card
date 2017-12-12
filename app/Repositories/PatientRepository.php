<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 12/07/2017
 * Time: 12:32 PM
 */

namespace App\Repositories;


use App\User;

class PatientRepository
{
    public function storeCcdProblem(User $patient, array $args)
    {
        if ( ! $args['code']) {
            return;
        }

        $newProblem = $patient->ccdProblems()->updateOrCreate([
            'name'           => $args['name'],
            'cpm_problem_id' => $args['cpm_problem_id'],
            'billable'       => $args['billable'] ?? null,
        ]);

        if ($args['code']) {
            $codeSystemId = getProblemCodeSystemCPMId([$args['code_system_name'], $args['code_system_oid']]);

            $code = $newProblem->codes()->create([
                'code_system_name'       => $args['code_system_name'],
                'code_system_oid'        => $args['code_system_oid'],
                'code'                   => $args['code'],
                'problem_code_system_id' => $codeSystemId,
            ]);
        }

        return $newProblem;
    }
}