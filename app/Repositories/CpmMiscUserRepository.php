<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories;

use App\Models\CPM\CpmMisc;
use App\Models\CPM\CpmMiscUser;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Collection;

class CpmMiscUserRepository
{
    public function addMiscToPatient($miscId, $userId, $instructionId = null)
    {
        if ( ! $this->patientHasMisc($userId, $miscId, $instructionId)) {
            $miscUser                     = new CpmMiscUser();
            $miscUser->patient_id         = $userId;
            $miscUser->cpm_misc_id        = $miscId;
            $miscUser->cpm_instruction_id = $instructionId;
            $miscUser->save();

            return $miscUser;
        }

        return $this->model()->where(['patient_id' => $userId, 'cpm_misc_id' => $miscId, 'cpm_instruction_id' => $instructionId])->first();
    }

    public function count()
    {
        return $this->model()->count();
    }

    public function editPatientMisc($userId, $miscId, $instructionId)
    {
        $this->removeInstructions($userId, $miscId);

        $this->model()->where(['patient_id' => $userId, 'cpm_misc_id' => $miscId])->delete();

        return $this->addMiscToPatient($miscId, $userId, $instructionId);
    }

    public function miscPatients($miscId)
    {
        return $this->model()->where(['cpm_misc_id' => $miscId])->get(['patient_id'])->map(function ($m) {
            return $m->patient_id;
        });
    }

    public function model()
    {
        return app(CpmMiscUser::class);
    }

    public function patientHasMisc($userId, $miscId, $instructionId = null)
    {
        return (bool) $this->model()->where(['patient_id' => $userId, 'cpm_misc_id' => $miscId, 'cpm_instruction_id' => $instructionId])->first();
    }

    public function patientMisc($userId, $miscTypeId = null)
    {
        $query = ['patient_id' => $userId];
        if ($miscTypeId) {
            $query['cpm_misc_id'] = $miscTypeId;
        }

        $relQuery = [
            'cpmMiscUserPivot.cpmInstruction',
            'cpmMiscUserPivot.cpmMisc',
        ];

        if (is_a($userId, User::class)) {
            $user = $userId;

            $user->loadMissing($relQuery);
        } else {
            $user = User::with($relQuery)->findOrFail($userId);
        }

        $miscData = $user->cpmMiscUserPivot->map(function ($userMisc) use ($user) {
            $misc = $userMisc->cpmMisc;

            if ($userMisc->cpmInstruction) {
                $instruction                 = $userMisc->cpmInstruction->toArray();
                $instruction['misc_user_id'] = $userMisc->id;
            }
    
            $misc['instructions'] = $instruction ?? [];

            return $misc;
        });

        if ($miscData->isEmpty() && $miscTypeId) {
            $misc                 = CpmMisc::findOrFail($miscTypeId);
            $misc['instructions'] = [];
            $miscCollection       = new Collection();
            $miscCollection->push($misc);

            return $miscCollection;
        }

        return $miscData;
    }

    public function removeInstructionFromPatientMisc($userId, $miscId, $instructionId)
    {
        $this->model()->where(['patient_id' => $userId, 'cpm_misc_id' => $miscId])->delete();

        return [
            'message' => 'successful',
        ];
    }

    public function removeInstructions($userId, $miscId)
    {
        $this->model()->where(['patient_id' => $userId, 'cpm_misc_id' => $miscId])->with('cpmInstruction')->get()->map(function ($m) {
            if ($m->cpmInstruction) {
                $m->cpmInstruction->delete();
            }
        });

        return [
            'message' => 'successful',
        ];
    }

    public function removeMiscFromPatient($miscId, $userId)
    {
        $this->model()->where(['patient_id' => $userId, 'cpm_misc_id' => $miscId])->delete();

        return [
            'message' => 'successful',
        ];
    }
}
