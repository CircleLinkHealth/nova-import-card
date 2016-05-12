<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 5/3/16
 * Time: 2:19 PM
 */

namespace App\Services\CPM;


use App\CarePlanTemplate;
use App\Contracts\Services\CpmModel;
use App\Models\CPM\CpmInstruction;
use App\User;

class CpmProblemService implements CpmModel
{
    public function syncWithUser(User $user, array $ids = [], $page = null, array $instructions)
    {
        $user->cpmProblems()->sync($ids);
        
        $instructionService = new CpmInstructionService();

        foreach ($ids as $problemId) {
            $relationship = 'cpmProblems';
            $entityId = $problemId;
            $entityForeign = 'cpm_problem_id';

            if (isset($instructions[$relationship][$entityId]))
            {
                $instructionInput = $instructions[$relationship][$entityId];

                $instructionService->syncWithUser($user, $relationship, $entityForeign, $entityId, $instructionInput);
            }
        }

        return true;
    }

    /**
     * @param User $patient
     * @return array|bool
     */
    public function getProblemsToMonitorWithDetails(User $patient)
    {
        $carePlan = $patient->service()->firstOrDefaultCarePlan($patient);

        //get the template
        $cptId = $carePlan->care_plan_template_id;
        $cpt = CarePlanTemplate::find($cptId);

        //get template's cpmProblems
        $cptProblems = $cpt->cpmProblems()->get();

        //get the User's cpmProblems
        $patientProblems = $patient->cpmProblems()->get();

        $intersection = $patientProblems->intersect($cptProblems)->all();

        return $intersection;
    }
}