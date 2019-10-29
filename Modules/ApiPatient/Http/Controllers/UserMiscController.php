<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ApiPatient\Http\Controllers;

use App\Services\CPM\CpmMiscService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class UserMiscController extends Controller
{
    /**
     * @var CpmMiscService
     */
    protected $miscService;

    public function __construct(CpmMiscService $miscService)
    {
        $this->miscService = $miscService;
    }

    public function addInstructionToMisc($userId, $miscId, Request $request)
    {
        $instructionId = $request->input('instructionId');
        if ($userId && $miscId && $instructionId) {
            return $this->miscService->editPatientMisc($userId, $miscId, $instructionId);
        }

        return \response('"miscId", "userId" and "instructionId" are important');
    }

    public function addMisc($userId, Request $request)
    {
        $miscId = $request->input('miscId');
        if ($userId && $miscId) {
            return $this->miscService->addMiscToPatient($miscId, $userId);
        }

        return \response('"miscId" and "userId" are important');
    }

    public function getMisc($userId)
    {
        if ($userId) {
            return $this->miscService->patientMisc($userId);
        }

        return \response('"userId" is important');
    }

    public function getMiscByType($userId, $miscTypeId)
    {
        if ($userId) {
            return $this->miscService->patientMiscByType($userId, $miscTypeId);
        }

        return \response('"userId" is important');
    }

    public function removeInstructionFromMisc($userId, $miscId, $instructionId)
    {
        if ($userId && $miscId && $instructionId) {
            return $this->miscService->removeInstructionFromPatientMisc($userId, $miscId, $instructionId);
        }

        return \response('"miscId", "userId" and "instructionId" are important');
    }

    public function removeMisc($userId, $miscId)
    {
        if ($userId && $miscId) {
            return $this->miscService->removeMiscFromPatient($miscId, $userId);
        }

        return \response('"miscId" and "userId" are important');
    }
}
