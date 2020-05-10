<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnswer;
use App\Services\EnrolleesSurveyService;
use App\Services\SurveyInvitationLinksService;
use App\Services\SurveyService;
use App\Survey;
use App\User;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\Entities\EnrollmentInvitationLetter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnrolleeSurveyController extends Controller
{
    /**
     * @var SurveyService
     */
    private $enrolleesSurveyService;
    /**
     * @var SurveyInvitationLinksService
     */
    private $surveyInvitationLinksService;

    /**
     * EnrolleeSurveyController constructor.
     */
    public function __construct(EnrolleesSurveyService $enrolleesSurveyService, SurveyInvitationLinksService $surveyInvitationLinksService)
    {
        $this->enrolleesSurveyService       = $enrolleesSurveyService;
        $this->surveyInvitationLinksService = $surveyInvitationLinksService;
    }

    /**
     * @param $userId
     * @param $surveyId
     * @throws \Exception
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function createEnrolleesSurveyUrl($userId, $surveyId)
    {
//        $user = User::whereId($userId)->firstOrFail();
//        $survey = Survey::whereId($surveyId)->firstOrFail();
        $user   = User::findOrFail($userId);
        $survey = Survey::findOrFail($surveyId);
        $url    = $this->surveyInvitationLinksService->createAndSaveUrl($user, $survey->name, true);
        Auth::loginUsingId($userId, true);

        return redirect($url);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEnrollableQuestionsData(Request $request)
    {
        $userId = $request->input('user_id');
        $user   = User::with('patientInfo')
            ->where('id', $userId)
            ->firstOrFail();

        $data = $this->enrolleesSurveyService->enrolleesQuestionsData($user);

        return response()->json([
            'success' => true,
            'data'    => $data,
        ], 200);
    }

    /**
     * @param $patientId
     * @param $surveyId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getSurvey($patientId, $surveyId)
    {
        $surveyData = $this->enrolleesSurveyService->getSurveyData($patientId, $surveyId);

        if ( ! $surveyData) {
            throw new \Error('Survey not found for patient '.$patientId);
        }

        return view('survey.Enrollees.index', [
            'data' => $surveyData->toArray(),
        ]);
    }

    public function showLogoutSuccessful($practiceId)
    {
        $practice     = Practice::findOrFail($practiceId);
        $practiceName = $practice->display_name;

        //default - should not be here
        $practiceLogoSrc = 'https://www.zilliondesigns.com/images/portfolio/healthcare-hospital/iStock-471629610-Converted.png';
        $practiceLetter  = EnrollmentInvitationLetter::wherePracticeId($practiceId)->first();
        if ($practiceLetter && ! empty($practiceLetter->practice_logo_src)) {
            $practiceLogoSrc = $practiceLetter->practice_logo_src;
        }

        return view('auth.logoutEnrollee', compact('practiceName', 'practiceLogoSrc'));
    }

    public function storeAnswer(StoreAnswer $request)
    {
        $input            = $request->all();
        $input['user_id'] = $input['patient_id'];

        return SurveyService::updateOrCreateAnswer($input);
    }
}
