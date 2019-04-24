<?php

namespace App\Services;

use App\Http\Requests\StoreVitalsAnswer;
use App\Survey;

class VitalsSurveyService
{
    public function getSurveyData($patientId)
    {
        $surveyId = Survey::where('name', '=', Survey::VITALS)->pluck('id')->first();

        return SurveyService::getSurveyData($patientId, $surveyId);

    }

    public function updateOrCreateAnswer(StoreVitalsAnswer $request)
    {
        $input            = $request->all();
        $input['user_id'] = $input['patient_id'];

        return SurveyService::updateOrCreateAnswer($input);

    }

}
