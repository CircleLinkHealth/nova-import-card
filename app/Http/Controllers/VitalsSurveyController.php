<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVitalsAnswer;
use App\Services\VitalsSurveyService;
use App\User;

class VitalsSurveyController extends Controller
{
    private $service;

    public function __construct(VitalsSurveyService $service)
    {
        $this->service = $service;
    }

    public function showWelcome($practiceId, $patientId)
    {
        $patient = User::findOrFail($patientId);

        return view('survey.vitals.welcome', [
            'patientsName' => $patient->display_name,
        ]);
    }

    public function showNotAuthorized($practiceId, $patientId)
    {
        $patient = User::with(['regularDoctor', 'billingProvider'])->findOrFail($patientId);

        if (!empty($patient->regularDoctorUser())) {
            $doctorsName = $patient->regularDoctorUser()->getFullName();
        }
        else if (!empty($patient->billingProviderUser())) {
            $doctorsName = $patient->billingProviderUser()->getFullName();
        }

        return view('survey.vitals.not-authorized', [
            'doctorsName' => $doctorsName ?? '',
        ]);
    }

    /**
     * Patient cannot access this route.
     * User must have `vitals-survey-complete` permission.
     *
     * @param $practiceId
     * @param $patientId
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getSurvey($practiceId, $patientId)
    {
        $userWithSurveyData = $this->service->getSurveyData($patientId);

        if (!$userWithSurveyData) {
            throw new \Error("Survey not found for patient " . $patientId);
        }

        return view('survey.vitals.index', [
            'data' => $userWithSurveyData->toArray(),
        ]);
    }

    /**
     * Patient cannot access this route.
     * User must have `vitals-survey-complete` permission.
     *
     * @param StoreVitalsAnswer $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeAnswer(StoreVitalsAnswer $request)
    {
        $answer = $this->service->updateOrCreateAnswer($request);

        if ( ! $answer) {
            return response()->json(['errors' => 'Answer was not created'], 400);
        }

        return response()->json([
            'created'       => true,
            'survey_status' => $answer,
        ], 200);

    }
}
