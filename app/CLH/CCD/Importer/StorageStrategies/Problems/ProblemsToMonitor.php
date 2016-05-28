<?php

namespace App\CLH\CCD\Importer\StorageStrategies\Problems;


use App\CarePlan;
use App\CLH\CCD\Importer\StorageStrategies\BaseStorageStrategy;
use App\CLH\Contracts\CCD\StorageStrategy;
use App\Models\CPM\CpmProblem;


class ProblemsToMonitor extends BaseStorageStrategy implements StorageStrategy
{
    public function import($cpmProblemIds = [])
    {
        if ( empty($cpmProblemIds) ) return;
        
        $cpmProblems = CpmProblem::findMany($cpmProblemIds);

        foreach ($cpmProblems as $cpmProblem)
        {
            $instructions = $cpmProblem->cpmInstructions()->get();

            $this->user->cpmProblems()->attach($cpmProblem->id, [
                'cpm_instruction_id' => $instructions->isEmpty() ?: $instructions[0]->id,
            ]);
        }
        
    }
}