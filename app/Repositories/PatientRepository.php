<?php

namespace App\Repositories;


use App\Exceptions\InvalidArgumentException;
use App\Patient;
use App\User;

class PatientRepository
{
    /**
     * Set a patient's ccm_status to paused.
     *
     * @param $user
     *
     * @return bool
     */
    public function pause($user)
    {
        if (is_a($user, User::class)) {
            $userId = $user->id;
        }

        if (is_numeric($user)) {
            $userId = $user;
        }

        if ( ! isset($userId)) {
            throw new InvalidArgumentException();
        }

        return Patient::where('user_id', $userId)->update(['ccm_status' => 'paused']);
    }

    public function storeCcdProblem(User $patient, array $args)
    {
        if ( ! $args['code']) {
            return;
        }

        $newProblem = $patient->ccdProblems()->updateOrCreate([
            'name'           => $args['name'],
            'cpm_problem_id' => empty($args['cpm_problem_id'])
                ? null
                : $args['cpm_problem_id'],
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