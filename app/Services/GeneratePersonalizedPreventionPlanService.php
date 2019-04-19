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
            ->updateOrCreate(
                [
                    'user_id' => $patient->id,
                ],
                [
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
                ]
            );

        return $patientPppData;
    }

    private function getAnswersToEvaluate()
    {
        $data                               = [];
        $data['age']                        = $this->answerForHraQuestionWithOrder(2);
        $data['sex']                        = $this->answerForHraQuestionWithOrder(4);
        $data['fruit_veggies']              = $this->answerForHraQuestionWithOrder(6);
        $data['whole_grain']                = $this->answerForHraQuestionWithOrder(7);
        $data['fatty_fried_foods']          = $this->answerForHraQuestionWithOrder(8);
        $data['candy_sugary_beverages']     = $this->answerForHraQuestionWithOrder(9);
        $data['diabetes']                   = $this->answerForHraQuestionWithOrder(16);
        $data['current_smoker']             = $this->answerForHraQuestionWithOrder(11);
        $data['smoker_interested_quitting'] = $this->answerForHraQuestionWithOrder(11, 'd');
        $data['alcohol_drinks']             = $this->answerForHraQuestionWithOrder(12, 'a');
        $data['recreational_drugs']         = $this->answerForHraQuestionWithOrder(13);
        $data['physical_activity']          = $this->answerForHraQuestionWithOrder(14);
        $data['sexually_active']            = $this->answerForHraQuestionWithOrder(15);
        $data['multiple_partners']          = $this->answerForHraQuestionWithOrder(15, 'a');
        $data['safe_sex']                   = $this->answerForHraQuestionWithOrder(15, 'b');
        $data['domestic_violence_screen']   = $this->answerForHraQuestionWithOrder(42);
        $data['difficulty_hearing']         = $this->answerForHraQuestionWithOrder(25);
        $data['fallen']                     = $this->answerForHraQuestionWithOrder(24);
        $data['bmi']                        = $this->answerForVitalsQuestionWithOrder(4);

        return $data;
    }

    private function answerForHraQuestionWithOrder($order, $subOrder = null)
    {
        $question = $this->hraQuestions->where('pivot.order', $order)->where('pivot.sub_order', $subOrder)->first();

        $answer = $this->hraAnswers->where('question_id', $question->id)->first();

        if ( ! $answer) {
            return [];
        }

        return array_key_exists('value', $answer->value)
            ? $answer->value['value']
            : $answer->value;
    }

    private function answerForVitalsQuestionWithOrder($order, $subOrder = null)
    {
        $question = $this->vitalsQuestions->where('pivot.order', $order)->where('pivot.sub_order', $subOrder)->first();

        $answer = $this->vitalsAnswers->where('question_id', $question->id)->first();

        if ( ! $answer) {
            return [];
        }

        return array_key_exists('value', $answer->value)
            ? $answer->value['value']
            : $answer->value;

    }
}


