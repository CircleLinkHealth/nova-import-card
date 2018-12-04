<?php

namespace App\Http\Controllers\Patient\Traits;

use Illuminate\Http\Request;

trait MiscTraits
{
    public function getMisc($userId)
    {
        if ($userId) {
            return $this->miscService->patientMisc($userId);
        } else {
            return $this->badRequest('"userId" is important');
        }
    }
    
    public function getMiscByType($userId, $miscTypeId)
    {
        if ($userId) {
            return $this->miscService->patientMiscByType($userId, $miscTypeId);
        } else {
            return $this->badRequest('"userId" is important');
        }
    }

    public function addMisc($userId, Request $request)
    {
        $miscId = $request->input('miscId');
        if ($userId && $miscId) {
            return $this->miscService->addMiscToPatient($miscId, $userId);
        } else {
            return $this->badRequest('"miscId" and "userId" are important');
        }
    }
    
    public function removeMisc($userId, $miscId)
    {
        if ($userId && $miscId) {
            return $this->miscService->removeMiscFromPatient($miscId, $userId);
        } else {
            return $this->badRequest('"miscId" and "userId" are important');
        }
    }
    
    public function addInstructionToMisc($userId, $miscId, Request $request)
    {
        $instructionId = $request->input('instructionId');
        if ($userId && $miscId && $instructionId) {
            return $this->miscService->editPatientMisc($userId, $miscId, $instructionId);
        } else {
            return $this->badRequest('"miscId", "userId" and "instructionId" are important');
        }
    }
    
    public function removeInstructionFromMisc($userId, $miscId, $instructionId)
    {
        if ($userId && $miscId && $instructionId) {
            return $this->miscService->removeInstructionFromPatientMisc($userId, $miscId, $instructionId);
        } else {
            return $this->badRequest('"miscId", "userId" and "instructionId" are important');
        }
    }
}
