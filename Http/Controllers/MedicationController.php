<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ApiPatient\Http\Controllers;

use App\Services\CPM\CpmMedicationGroupService;
use App\Services\CPM\CpmMedicationService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class MedicationController extends Controller
{
    /**
     * @var CpmMedicationGroupService
     */
    protected $medicationGroupService;
    /**
     * @var CpmMedicationService
     */
    protected $medicationService;

    public function __construct(CpmMedicationService $medicationService, CpmMedicationGroupService $medicationGroupService)
    {
        $this->medicationService      = $medicationService;
        $this->medicationGroupService = $medicationGroupService;
    }

    public function addMedication($userId, Request $request)
    {
        if ($userId) {
            $medication             = $this->retrieveMedication($request);
            $medication->patient_id = $userId;

            return $this->medicationService->repo()->addMedicationToPatient($medication);
        }

        return \response('"userId" is important');
    }

    public function editMedication($userId, $id, Request $request)
    {
        if ($userId) {
            $medication             = $this->retrieveMedication($request);
            $medication->id         = $id;
            $medication->patient_id = $userId;

            return $this->medicationService->editPatientMedication($medication);
        }

        return \response('"userId" is important');
    }

    public function getMedication($userId)
    {
        if ($userId) {
            return $this->medicationService->patientMedicationPaginated($userId);
        }

        return \response('"userid" is important');
    }

    public function getMedicationGroups($userId)
    {
        if ($userId) {
            return $this->medicationGroupService->repo()->patientGroups($userId);
        }

        return \response('"userid" is important');
    }

    public function removeMedication($userId, $medicationId)
    {
        if ($userId) {
            return $this->medicationService->repo()->removeMedicationFromPatient($medicationId, $userId);
        }

        return \response('"userId" is important');
    }

    public function retrieveMedication(Request $request)
    {
        $medication                        = new \CircleLinkHealth\SharedModels\Entities\Medication();
        $medication->active                = $request->input('active');
        $medication->medication_import_id  = $request->input('medication_import_id');
        $medication->ccda_id               = $request->input('ccda_id');
        $medication->vendor_id             = $request->input('vendor_id');
        $medication->ccd_medication_log_id = $request->input('ccd_medication_log_id');
        $medication->medication_group_id   = $request->input('medication_group_id');
        $medication->name                  = $request->input('name');
        $medication->sig                   = $request->input('sig');
        $medication->code                  = $request->input('code');
        $medication->code_system           = $request->input('code_system');
        $medication->code_system_name      = $request->input('code_system_name');

        return $medication;
    }
}
