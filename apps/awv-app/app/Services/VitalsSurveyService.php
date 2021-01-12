<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\Http\Requests\StoreVitalsAnswer;
use App\Survey;

class VitalsSurveyService
{
    public function getSurveyData($patientId)
    {
        return SurveyService::getCurrentSurveyData($patientId, Survey::VITALS);
    }

    public function updateOrCreateAnswer(StoreVitalsAnswer $request)
    {
        $input            = $request->all();
        $input['user_id'] = $input['patient_id'];

        return SurveyService::updateOrCreateAnswer($input);
    }
}
