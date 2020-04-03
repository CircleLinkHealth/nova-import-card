<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnswer;
use App\Services\SurveyService;
use App\Survey;
use Auth;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $user = User::with('patientInfo')
            ->where('id', $userId)
            ->firstOrFail();

        $birthDate = '';
        if (optional($user->patientInfo)->birth_date) {
            $birthDate = Carbon::parse($user->patientInfo->birth_date)->toDateString();
        }

        // It can be empty. Its ok.
        $primaryPhoneNumber = $user->phoneNumbers->where('is_primary', '=', true)->first()->number;
        $isSurveyOnly = $user->hasRole('survey-only');

        $letterLink = '';

        if ($isSurveyOnly) {
            $id = DB::table('enrollees')->where('user_id', $userId)->select('id')->first()->id;

            $letter = DB::table('enrollables_invitation_links')
                ->where('invitationable_id', $id)
                ->select('url')
                ->first();

            $letterLink = $letter->url;
        }


        $data = [
            'dob' => $birthDate,
            'address' => $user->address,
            'patientEmail' => $user->email,
            'preferredContactNumber' => !empty($primaryPhoneNumber) ? $primaryPhoneNumber : [],
            'isSurveyOnlyRole' => $isSurveyOnly,
            'letterLink' => $letterLink
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
        ], 200);
    }
}
