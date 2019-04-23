<?php

namespace App\Services;

use App\Http\Requests\StoreVitalsAnswer;
use App\SurveyInstance;
use App\User;

class VitalsSurveyService
{
    public function getSurveyData($patientId)
    {
        //todo: implement scopeOfType() if we do add type field
        $patientWithSurveyData = User::with(
            [
                'surveyInstances' => function (SurveyInstance $instance) {
                    $instance->current()
                             ->with([
                                 'survey',
                                 'questions' => function ($question) {
                                     $question->with(['questionGroup', 'type.questionTypeAnswers']);
                                 },
                             ]);

                },
                'answers',
            ])
                                     ->whereHas('surveys', function ($survey) {
                                         $survey->where('status', '!=', SurveyInstance::COMPLETED);
                                     })
                                     ->whereHas('surveyInstances', function ($instance) {
                                         $instance->current();
                                     })
                                     ->where('id', $patientId)
                                     ->first();


        return $patientWithSurveyData;

    }

    public function updateOrCreateAnswer(StoreVitalsAnswer $request)
    {
        $input  = $request->all();
        $input['user_id'] = $input['patient_id'];

        return SurveyService::updateOrCreateAnswer($input);

    }

}
