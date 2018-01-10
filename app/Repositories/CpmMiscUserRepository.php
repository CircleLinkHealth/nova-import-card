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
        return $this->model()->where([ 'patient_id' => $userId ])->with(['cpmMisc', 'cpmInstruction'])->get()->map(function ($u) {
            $misc = $u->cpmMisc;
            if ($u->cpmInstruction) {
                $u->cpmInstruction['misc_user_id'] = $u->id;
            }
            $misc['instruction'] = $u->cpmInstruction;
            return $misc;
        });
    }
    
    public function patientHasMisc($userId, $miscId) {
        return !!$this->model()->where([ 'patient_id' => $userId, 'cpm_misc_id' => $miscId ])->first();
    }

    public function addMiscToPatient($miscId, $userId) {
        if (!$this->patientHasMisc($userId, $miscId)) {
            $miscUser = new CpmMiscUser();
            $miscUser->patient_id = $userId;
            $miscUser->cpm_misc_id = $miscId;
            $miscUser->save();
            return $miscUser;
        }
        else return $this->model()->where([ 'patient_id' => $userId, 'cpm_misc_id' => $miscId ])->first();
    }

    public function removeMiscFromPatient($miscId, $userId) {
        $this->model()->where([ 'patient_id' => $userId, 'cpm_misc_id' => $miscId ])->delete();
        return [
            'message' => 'successful'
        ];
    }
    
    public function editPatientMisc($userId, $miscId, $instructionId) {
        if (!!$this->model()->where([ 'patient_id' => $userId, 'cpm_misc_id' => $miscId ])->first()) {
            $this->model()->where([ 'patient_id' => $userId, 'cpm_misc_id' => $miscId ])->update([
                'cpm_instruction_id' => $instructionId
            ]);
        }
        return $this->model()->where([ 'patient_id' => $userId, 'cpm_misc_id' => $miscId ])->first();
    }
}