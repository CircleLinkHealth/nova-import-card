<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Filters\PatientFilters;
use App\Http\Controllers\Patient\Traits\AllergyTraits;
use App\Http\Controllers\Patient\Traits\AppointmentTraits;
use App\Http\Controllers\Patient\Traits\BiometricUserTraits;
use App\Http\Controllers\Patient\Traits\CcdProblemTraits;
use App\Http\Controllers\Patient\Traits\CpmProblemUserTraits;
use App\Http\Controllers\Patient\Traits\LifestyleTraits;
use App\Http\Controllers\Patient\Traits\MedicationTraits;
use App\Http\Controllers\Patient\Traits\MiscTraits;
use App\Http\Controllers\Patient\Traits\NoteTraits;
use App\Http\Controllers\Patient\Traits\ProviderInfoTraits;
use App\Http\Controllers\Patient\Traits\SymptomTraits;
use App\Services\AppointmentService;
use App\Services\CCD\CcdAllergyService;
use App\Services\CCD\CcdProblemService;
use App\Services\CPM\CpmBiometricService;
use App\Services\CPM\CpmLifestyleService;
use App\Services\CPM\CpmMedicationGroupService;
use App\Services\CPM\CpmMedicationService;
use App\Services\CPM\CpmMiscService;
use App\Services\CPM\CpmProblemUserService;
use App\Services\CPM\CpmSymptomService;
use App\Services\NoteService;
use App\Services\PatientService;
use App\Services\ProviderInfoService;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    use ProviderInfoTraits,
        AppointmentTraits,
        AllergyTraits,
        CcdProblemTraits,
        CpmProblemUserTraits,
        BiometricUserTraits,
        MedicationTraits,
        SymptomTraits,
        LifestyleTraits,
        NoteTraits,
        MiscTraits;
    private $allergyService;
    private $appointmentService;
    private $biometricUserService;
    private $ccdProblemService;
    private $cpmProblemUserService;
    private $lifestyleService;
    private $medicationGroupService;
    private $medicationService;
    private $miscService;
    private $noteService;

    private $patientService;
    private $providerService;
    private $symptomService;

    /**
     * CpmProblemController constructor.
     */
    public function __construct(
        PatientService $patientService,
        ProviderInfoService $providerService,
        AppointmentService $appointmentService,
        CcdAllergyService $allergyService,
        CcdProblemService $ccdProblemService,
        CpmProblemUserService $cpmProblemUserService,
        CpmBiometricService $biometricUserService,
        CpmMedicationService $medicationService,
        CpmMedicationGroupService $medicationGroupService,
        CpmSymptomService $symptomService,
        CpmLifestyleService $lifestyleService,
        CpmMiscService $miscService,
        NoteService $noteService
    ) {
        $this->patientService         = $patientService;
        $this->providerService        = $providerService;
        $this->appointmentService     = $appointmentService;
        $this->allergyService         = $allergyService;
        $this->ccdProblemService      = $ccdProblemService;
        $this->cpmProblemUserService  = $cpmProblemUserService;
        $this->biometricUserService   = $biometricUserService;
        $this->medicationService      = $medicationService;
        $this->medicationGroupService = $medicationGroupService;
        $this->symptomService         = $symptomService;
        $this->lifestyleService       = $lifestyleService;
        $this->miscService            = $miscService;
        $this->noteService            = $noteService;
    }

    public function getPatient($userId)
    {
        return response()->json($this->patientService->getPatientByUserId($userId));
    }

    /**
     * returns a list of CPM Problems in the system.
     */
    public function index(Request $request, PatientFilters $filters)
    {
        return $this->patientService->patients($filters);
    }
}
