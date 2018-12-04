<?php

namespace App\Repositories;

use App\User;
use App\Patient;
use App\Models\CPM\CpmMisc;
use App\Models\CPM\CpmMiscUser;
use Illuminate\Support\Collection;

class CpmMiscUserRepository
{
    public function model()
    {
        return app(CpmMiscUser::class);
    }

    public function count()
    {
        return $this->model()->count();
    }
    
    public function miscPatients($miscId)
    {
        return $this->model()->where([ 'cpm_misc_id' => $miscId ])->get(['patient_id'])->map(function ($m) {
            return $m->patient_id;
        });
    }

    public function patientMisc($userId, $miscTypeId = null)
    {
        $query = [ 'patient_id' => $userId ];
        if ($miscTypeId) {
            $query['cpm_misc_id'] = $miscTypeId;
        }
        $miscData = $this->model()->where($query)->groupBy('cpm_misc_id')->with(['cpmMisc'])->get()->map(function ($u) use ($userId) {
            $misc = $u->cpmMisc;
            $misc['instructions'] = array_values($this->model()
                                            ->where([ 'patient_id' => $userId, 'cpm_misc_id' => $misc->id ])
                                            ->orderBy('id', 'desc')
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

        if (!$miscData->count() && $miscTypeId) {
            $misc = CpmMisc::findOrFail($miscTypeId);
            $misc['instructions'] = [];
            $miscCollection = new Collection();
            $miscCollection->push($misc);
            return $miscCollection;
        }

        return $miscData;
    }
    
    public function patientHasMisc($userId, $miscId, $instructionId = null)
    {
        return !!$this->model()->where([ 'patient_id' => $userId, 'cpm_misc_id' => $miscId, 'cpm_instruction_id' => $instructionId ])->first();
    }

    public function addMiscToPatient($miscId, $userId, $instructionId = null)
    {
        if (!$this->patientHasMisc($userId, $miscId, $instructionId)) {
            $miscUser = new CpmMiscUser();
            $miscUser->patient_id = $userId;
            $miscUser->cpm_misc_id = $miscId;
            $miscUser->cpm_instruction_id = $instructionId;
            $miscUser->save();
            return $miscUser;
        } else {
            return $this->model()->where([ 'patient_id' => $userId, 'cpm_misc_id' => $miscId, 'cpm_instruction_id' => $instructionId ])->first();
        }
    }

    public function removeMiscFromPatient($miscId, $userId)
    {
        $this->model()->where([ 'patient_id' => $userId, 'cpm_misc_id' => $miscId ])->delete();
        return [
            'message' => 'successful'
        ];
    }
    
    public function removeInstructionFromPatientMisc($userId, $miscId, $instructionId)
    {
        $this->model()->where([ 'patient_id' => $userId, 'cpm_misc_id' => $miscId ])->delete();
        return [
            'message' => 'successful'
        ];
    }

    public function removeInstructions($userId, $miscId)
    {
        $this->model()->where([ 'patient_id' => $userId, 'cpm_misc_id' => $miscId ])->with('cpmInstruction')->get()->map(function ($m) {
            if ($m->cpmInstruction) {
                $m->cpmInstruction->delete();
            }
        });
        return [
            'message' => 'successful'
        ];
    }
    
    public function editPatientMisc($userId, $miscId, $instructionId)
    {
        $this->removeInstructions($userId, $miscId);

        $this->model()->where([ 'patient_id' => $userId, 'cpm_misc_id' => $miscId ])->delete();
        
        return $this->addMiscToPatient($miscId, $userId, $instructionId);
    }
}
