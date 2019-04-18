<?php

namespace App\Services;


use App\Survey;
use App\User;
use Carbon\Carbon;

class GeneratePersonalizedPreventionPlanService
{
    /**
     * @var \App\User
     */
    protected $patient;
    protected $hraInstance;
    protected $vitalsInstance;
    protected $hraQuestions;
    protected $vitalsQuestions;
    protected $hraAnswers;
    protected $vitalsAnswers;


    public function __construct(User $patient)
    {
        $this->patient         = $patient;
        $this->hraInstance     = $patient->surveyInstances->where('survey.name', Survey::HRA)->first();
        $this->vitalsInstance  = $patient->surveyInstances->where('survey.name', Survey::VITALS)->first();
        $this->hraAnswers      = $patient->answers->where('survey_instance_id', $this->hraInstance->id);
        $this->vitalsAnswers   = $patient->answers->where('survey_instance_id', $this->vitalsInstance->id);
        $this->hraQuestions    = $this->hraInstance->questions;
        $this->vitalsQuestions = $this->vitalsInstance->questions;

        //@todo::remove this when done dev
        $this->generateData($patient);
    }

    public function generateData($patient)
    {
        $birthDate = new Carbon('2019-01-01');

        $patientPppData = $this->patient
            ->personalizedPreventionPlan()
            ->create([
                'user_id'          => $patient->id,
                'display_name'     => $patient->display_name,
                'birth_date'       => /*$patient->patientInfo->birth_date*/
                    $birthDate,
                'address'          => $patient->address,
                'city'             => $patient->city,
                'state'            => $patient->state,
                'hra_answers'      => $this->hraAnswers,
                'vitals_answers'   => $this->vitalsAnswers,
                'billing_provider' => /*$patient->billingProvider->member_user_id*/
                    'Kirkillis',
                'answers_for_eval' => $this->getAnswersToEvaluate(),

            ]);

        return $patientPppData;
    }

    private function getAnswersToEvaluate()
    {
        $data                           = [];
        $data['age']                    = $this->answerForHraQuestionWithId(2);
        $data['sex']                    = $this->answerForHraQuestionWithId(4);
        $data['fruit_veggies']          = $this->answerForHraQuestionWithId(6);
        $data['whole_grain']            = $this->answerForHraQuestionWithId(7);
        $data['fatty_fried_foods']      = $this->answerForHraQuestionWithId(8);
        $data['candy_sugary_beverages'] = $this->answerForHraQuestionWithId(9);
        $data['diabetes']               = $this->answerForHraQuestionWithId(24);
        $data['current_smoker']         = $this->answerForHraQuestionWithId(11);
        $data['already_quit_smoking']   = $this->answerForHraQuestionWithId(15);

        return $data;
    }

    private function answerForHraQuestionWithId($id)
    {
        $question = $this->hraQuestions->where('id', $id)->first();

        $answer = $this->hraAnswers->where('question_id', $question->id)->first();

        if ( ! $answer) {
            return [];
        }

        return array_key_exists('value', $answer->value)
            ? $answer->value['value']
            : $answer->value;
    }
}


