<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use App\Models\ProblemCode;

class ProblemCodeObserver
{
    /**
     * Listen to the ProblemCode saving event.
     *
     * @param ProblemCode $problemCode
     */
    public function saving(ProblemCode $problemCode)
    {
        if ( ! $problemCode->problem_code_system_id) {
            $problemCode->problem_code_system_id = getProblemCodeSystemCPMId([
                $problemCode->code_system_name,
                $problemCode->code_system_oid,
            ]);
        }
    }
}
