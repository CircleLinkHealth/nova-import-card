<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Patient\Traits;

use Illuminate\Http\Request;

trait MiscTraits
{
    public function addInstructionToMisc($userId, $miscId, Request $request)
    {
        $instructionId = $request->input('instructionId');
        if ($userId && $miscId && $instructionId) {
            return $this->miscService->editPatientMisc($userId, $miscId, $instructionId);
        }

        return $this->badRequest('"miscId", "userId" and "instructionId" are important');
    }

    public function addMisc($userId, Request $request)
    {
        $miscId = $request->input('miscId');
        if ($userId && $miscId) {
            return $this->miscService->addMiscToPatient($miscId, $userId);
        }

        return $this->badRequest('"miscId" and "userId" are important');
    }

    public function getMisc($userId)
    {
        if ($userId) {
            return $this->miscService->patientMisc($userId);
        }

        return $this->badRequest('"userId" is important');
    }

    public function getMiscByType($userId, $miscTypeId)
    {
        if ($userId) {
            return $this->miscService->patientMiscByType($userId, $miscTypeId);
        }

        return $this->badRequest('"userId" is important');
    }

    public function removeInstructionFromMisc($userId, $miscId, $instructionId)
    {
        if ($userId && $miscId && $instructionId) {
            return $this->miscService->removeInstructionFromPatientMisc($userId, $miscId, $instructionId);
        }

        return $this->badRequest('"miscId", "userId" and "instructionId" are important');
    }

    public function removeMisc($userId, $miscId)
    {
        if ($userId && $miscId) {
            return $this->miscService->removeMiscFromPatient($miscId, $userId);
        }

        return $this->badRequest('"miscId" and "userId" are important');
    }
}
