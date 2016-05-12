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

        foreach ($ids as $problemId) {
            $relationship = 'cpmProblems';
            $entityId = $problemId;
            $entityForeign = 'cpm_problem_id';

            if (isset($instructions[$relationship][$entityId])) {
                $instructionInput = $instructions[$relationship][$entityId];


                $userRel = $user->{$relationship}()
                    ->where($entityForeign, '=', $entityId)
                    ->whereNotNull('cpm_instruction_id')
                    ->first();

                if (!empty($userRel)) {
                    $oldInstructionId = $userRel->pivot->cpm_instruction_id;

                    $oldInstruction = CpmInstruction::find($oldInstructionId);

                    if (preg_replace("/\r|\n/", "", trim($oldInstruction->name)) == preg_replace("/\r|\n/", "", trim($instructionInput))) continue;

                    if ($oldInstruction->is_default) {
                        $newInstruction = CpmInstruction::create([
                            'name' => $instructionInput,
                        ]);

                        $user->{$relationship}()->updateExistingPivot($entityId, [
                            'cpm_instruction_id' => $newInstruction->id,
                        ]);

                        continue;
                    }

                    if (!$oldInstruction->is_default) {
                        $oldInstruction->update([
                            'name' => $instructionInput,
                        ]);

                        continue;
                    }
                }


                $template = $user->service()
                    ->firstOrDefaultCarePlan($user)
                    ->carePlanTemplate()
                    ->first();

                $templateRel = $template->{$relationship}()
                    ->where($entityForeign, '=', $entityId)
                    ->whereNotNull('cpm_instruction_id')
                    ->first();

                if (!empty($templateRel)) {
                    $oldInstructionId = $templateRel->pivot->cpm_instruction_id;

                    $oldInstruction = CpmInstruction::find($oldInstructionId);

                    //If the user does not have that instruction id,
                    //but the care plan does, and they are the same
                    //then just attach the instr_id to the user-cpmentity relationship
                    if (preg_replace("/\r|\n/", "", trim($oldInstruction->name)) == preg_replace("/\r|\n/", "", trim($instructionInput))) {
                        $user->{$relationship}()->updateExistingPivot($entityId, [
                            'cpm_instruction_id' => $oldInstruction->id,
                        ]);
                        continue;
                    }

                    if ($oldInstruction->is_default) {
                        $newInstruction = CpmInstruction::create([
                            'name' => $instructionInput,
                        ]);

                        $user->{$relationship}()->updateExistingPivot($entityId, [
                            'cpm_instruction_id' => $newInstruction->id,
                        ]);

                        continue;
                    }
                }
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