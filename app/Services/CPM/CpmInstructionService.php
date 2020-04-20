<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\CPM;

use App\Repositories\CpmInstructionRepository;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\CpmInstruction;
use Illuminate\Support\Str;

class CpmInstructionService
{
    private $instructionsRepo;
    private $userRepo;

    public function __construct(CpmInstructionRepository $instructionsRepo)
    {
        $this->instructionsRepo = $instructionsRepo;
    }

    public function create($name)
    {
        if ($name) {
            $instruction             = new CpmInstruction();
            $instruction->name       = $name;
            $instruction->is_default = 0;
            $instruction->save();

            return $instruction;
        }
    }

    public function edit($id, $text)
    {
        if ($id && $text) {
            $query = CpmInstruction::where('id', $id);
            $query->update(['name' => $text]);

            return $query->first();
        }
    }

    public function instruction($id)
    {
        $instruction = $this->repo()->model()->find($id);
        if ($instruction) {
            return $this->setupInstruction($instruction);
        }

        return null;
    }

    public function instructions()
    {
        $instructions = $this->repo()->model()->paginate(15);
        $instructions->getCollection()->transform([$this, 'setupInstruction']);

        return $instructions;
    }

    public function repo()
    {
        return $this->instructionsRepo;
    }

    public function setupInstruction($value)
    {
        $value->problems = $value->cpmProblems()->get(['cpm_problems.id'])->map(function ($p) {
            return $p->id;
        });

        return $value;
    }

    public function syncWithUser(User $user, $relationship, $entityForeign, $entityId, $instructionInput)
    {
        if ( ! method_exists($user, $relationship)) {
            throw new \Exception('Relationship does not exist', 500);
        }

        $pivotTableName = Str::snake($relationship).'_users';

        $userRel = $user->{$relationship}()
            ->where($entityForeign, '=', $entityId)
            ->whereNotNull('cpm_instruction_id')
            ->first();

        if ( ! empty($userRel)) {
            $oldInstructionId = $userRel->pivot->cpm_instruction_id;

            $oldInstruction = CpmInstruction::find($oldInstructionId);

            if (empty($oldInstruction)) {
                return;
            }

            if (preg_replace("/\r|\n/", '', trim($oldInstruction->name)) == preg_replace("/\r|\n/", '', trim($instructionInput))) {
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

        if ( ! empty($templateRel)) {
            $oldInstructionId = $templateRel->pivot->cpm_instruction_id;

            $oldInstruction = CpmInstruction::find($oldInstructionId);

            //If the user does not have that instruction id,
            //but the care plan does, and they are the same
            //then just attach the instr_id to the user-cpmentity relationship
            if (preg_replace("/\r|\n/", '', trim($oldInstruction->name)) == preg_replace("/\r|\n/", '', trim($instructionInput))) {
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

        if ( ! empty(trim($instructionInput))) {
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
