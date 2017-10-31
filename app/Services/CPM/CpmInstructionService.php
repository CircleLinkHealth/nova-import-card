<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 5/12/16
 * Time: 3:13 AM
 */

namespace App\Services\CPM;

use App\Models\CPM\CpmInstruction;
use App\User;

class CpmInstructionService
{
    public function syncWithUser(User $user, $relationship, $entityForeign, $entityId, $instructionInput)
    {
        if (!method_exists($user, $relationship)) {
            throw new \Exception('Relationship does not exist', 500);
        }
        
        $pivotTableName = snake_case($relationship).'_users';

        $userRel = $user->{$relationship}()
            ->where($entityForeign, '=', $entityId)
            ->whereNotNull('cpm_instruction_id')
            ->first();

        if (!empty($userRel)) {
            $oldInstructionId = $userRel->pivot->cpm_instruction_id;

            $oldInstruction = CpmInstruction::find($oldInstructionId);

            if (empty($oldInstruction)) {
                return;
            }

            if (preg_replace("/\r|\n/", "", trim($oldInstruction->name)) == preg_replace("/\r|\n/", "", trim($instructionInput))) {
                return;
            }

            $userWithSameInstr = \DB::table($pivotTableName)
                ->where('cpm_instruction_id', '=', 1025)
                ->count();

            if ($oldInstruction->is_default || $userWithSameInstr > 1) {
                $newInstruction = CpmInstruction::create([
                    'name' => $instructionInput,
                ]);

                $user->{$relationship}()->updateExistingPivot($entityId, [
                    'cpm_instruction_id' => $newInstruction->id,
                ]);

                return;
            }

            $oldInstruction->update([
                'name' => $instructionInput,
            ]);

            return;
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
                return;
            }

            if ($oldInstruction->is_default) {
                $newInstruction = CpmInstruction::create([
                    'name' => $instructionInput,
                ]);

                $user->{$relationship}()->updateExistingPivot($entityId, [
                    'cpm_instruction_id' => $newInstruction->id,
                ]);

                return;
            }
        }

        if (!empty(trim($instructionInput))) {
            $newInstruction = CpmInstruction::create([
                'name' => $instructionInput,
            ]);

            $user->{$relationship}()->updateExistingPivot($entityId, [
                'cpm_instruction_id' => $newInstruction->id,
            ]);

            return;
        }
    }
}
