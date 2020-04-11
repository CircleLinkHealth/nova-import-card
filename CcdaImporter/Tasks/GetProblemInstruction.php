<?php


namespace CircleLinkHealth\Eligibility\CcdaImporter\Tasks;


use CircleLinkHealth\SharedModels\Entities\CpmProblem;

class GetProblemInstruction
{
    public function for($newProblem) {
        $cpmProblems = \Cache::remember(
            sha1('all_cpm_problems_keyed_by_id'),
            2,
            function () {
                return CpmProblem::get()->keyBy('id');
            }
        );
    
        $cpmProblem = $newProblem->cpm_problem_id
            ? $cpmProblems[$newProblem->cpm_problem_id]
            : null;
    
        return optional($cpmProblem)->instruction();
    }
}