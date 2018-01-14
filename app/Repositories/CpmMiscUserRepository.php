<?php

namespace App\Repositories;

use App\User;
use App\Patient;
use App\Models\CPM\CpmMiscUser;

class CpmMiscUserRepository
{
    public function model()
    {
        return app(CpmMiscUser::class);
    }

    public function count() {
        return $this->model()->count();
    }
    
    public function miscPatients($miscId) {
        return $this->model()->where([ 'cpm_misc_id' => $miscId ])->get(['patient_id'])->map(function ($m) {
            return $m->patient_id;
        });
    }

    public function patientMisc($userId) {
        return $this->model()->where([ 'patient_id' => $userId ])->groupBy('cpm_misc_id')->with(['cpmMisc'])->get()->map(function ($u) use ($userId) {
            $misc = $u->cpmMisc;
            $misc['instructions'] = array_values($this->model()
                                            ->where([ 'patient_id' => $userId, 'cpm_misc_id' => $misc->id ])
                                            ->with('cpmInstruction')->get()->map(function ($cu) {
                                                if ($cu->cpmInstruction) {
                                                    $cu->cpmInstruction['misc_user_id'] = $cu->id;
                                                }
                                                return $cu->cpmInstruction;
                                            })->filter(function ($i) {
                                                return !!$i;
                                            })->toArray());
            return $misc;
        });
    }
    
    public function patientHasMisc($userId, $miscId, $instructionId = null) {
        return !!$this->model()->where([ 'patient_id' => $userId, 'cpm_misc_id' => $miscId, 'cpm_instruction_id' => $instructionId ])->first();
    }

    public function addMiscToPatient($miscId, $userId, $instructionId = null) {
        if (!$this->patientHasMisc($userId, $miscId, $instructionId)) {
            $miscUser = new CpmMiscUser();
            $miscUser->patient_id = $userId;
            $miscUser->cpm_misc_id = $miscId;
            $miscUser->cpm_instruction_id = $instructionId;
            $miscUser->save();
            return $miscUser;
        }
        else return $this->model()->where([ 'patient_id' => $userId, 'cpm_misc_id' => $miscId, 'cpm_instruction_id' => $instructionId ])->first();
    }

    public function removeMiscFromPatient($miscId, $userId) {
        $this->model()->where([ 'patient_id' => $userId, 'cpm_misc_id' => $miscId ])->delete();
        return [
            'message' => 'successful'
        ];
    }
    
    public function removeInstructionFromPatientMisc($miscId, $userId, $instructionId) {
        $this->model()->where([ 'patient_id' => $userId, 'cpm_misc_id' => $miscId, 'cpm_instruction_id' => $instructionId ])->delete();
        return [
            'message' => 'successful'
        ];
    }
    
    public function editPatientMisc($userId, $miscId, $instructionId) {
        if (!!$this->model()->where([ 'patient_id' => $userId, 'cpm_misc_id' => $miscId, 'cpm_instruction_id' => null ])->first()) {
            $this->model()->where([ 'patient_id' => $userId, 'cpm_misc_id' => $miscId, 'cpm_instruction_id' => null ])->update([
                'cpm_instruction_id' => $instructionId
            ]);
            return $this->model()->where([ 'patient_id' => $userId, 'cpm_misc_id' => $miscId, 'cpm_instruction_id' => $instructionId ])->first();
        }
        else {
            $miscUser = $this->model()->where([ 'patient_id' => $userId, 'cpm_misc_id' => $miscId, 'cpm_instruction_id' => $instructionId ])->first();
            if (!$miscUser) {
                return $this->addMiscToPatient($userId, $miscId, $instructionId);
            }
            return $miscUser;
        }
    }
}