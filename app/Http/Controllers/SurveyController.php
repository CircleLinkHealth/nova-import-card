<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnswer;
use App\Services\SurveyService;
use App\Survey;
use Auth;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    private $service;

    public function __construct(SurveyService $service)
    {
        $this->service = $service;
    }

    public function getCurrentSurvey($patientId)
    {
        //no need to have this check here
        if (!Auth::check()) {
            return redirect()->route('survey.vitals.welcome', ['patientId' => $patientId]);
        }

        $surveyData = $this->service->getCurrentSurveyData($patientId, Survey::HRA);

        if (!$surveyData) {
            throw new \Error("Survey not found for patient " . $patientId);
        }

        return view('survey.hra.index', [
            'data' => $surveyData->toArray(),
        ]);
    }

    public function getSurvey($patientId, $surveyId)
    {
        //no need to have this check here
//        if ( ! Auth::check()) {
//            return redirect()->route('survey.vitals.welcome', ['patientId' => $patientId]);
//        }

//        if (!Auth::user()->hasPermissionForSite('vitals-survey-complete', $practiceId)) {
//            return redirect()->route('survey.vitals.not.authorized', ['practiceId' => $practiceId, 'patientId' => $patientId]);
//        }

        $surveyData = $this->service->getSurveyData($patientId, $surveyId);

        if (!$surveyData) {
            throw new \Error("Survey not found for patient " . $patientId);
        }

        if ($surveyData->surveyInstances[0]->survey->name === 'Enrollees') {
            return view('survey.Enrollees.index', [
                'data' => $surveyData->toArray(),
            ]);
        }

        return view('survey.hra.index', [
            'data' => $surveyData->toArray(),
        ]);
    }

    public function storeAnswer(StoreAnswer $request)
    {
        $input = $request->all();
        $input['user_id'] = $input['patient_id'];

        $answer = $this->service->updateOrCreateAnswer($input);

        if (!$answer) {
            return response()->json(['errors' => 'Answer was not created'], 400);
        }

        return response()->json([
            'created' => true,
            'survey_status' => $answer,
        ], 200);

    }

    public function getEnrollableQuestionsData(Request $request)
    {
        $userId = $request->input('user_id');
        $surveyInstanceId = $request->input('survey_instance_id');
        $user = User::with('patientInfo')
            ->where('id', $userId)
            ->firstOrFail();

        $birthDate = !empty($user->patientInfo->birth_date) ? Carbon::parse($user->patientInfo->birth_date)->toDateString() : '';
        // It can be empty. Its ok.
        $primaryPhoneNumber = $user->phoneNumbers->where('is_primary','=' ,true)->first()->number;

        $data = [
            'dob' => $birthDate,
            'address' => $user->address,
            'patientEmail' => $user->email,
            'preferredContactNumber' => !empty($primaryPhoneNumber) ? $primaryPhoneNumber : [],
            'isSurveyOnlyRole' => $user->hasRole('survey-only'),
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
        ], 200);
    }
}
