<?php

namespace App\Services;


use App\User;

class SurveyService
{
    public function getSurveyData($patientId){

        $data = User::with(['surveys.instances.questions.type.questionTypeAnswers', 'answers'])
            ->whereHas()
            ->where()
            ->get();

        return $data;

    }

    public function storeAnswer($input){

        $answer = User::findOrFail($input['user_id'])->answers()->create($input);

        //change survey status?
        //change last question answered field

        return $answer;

    }

    //update?
}