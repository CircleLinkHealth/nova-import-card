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
    protected $date;


    /**
     * GeneratePersonalizedPreventionPlanService constructor.
     *
     * @param User $patient
     * @param $date
     */
    public function __construct($patient, $date)
    {

        $this->patient = $patient;
        $this->date    = Carbon::parse($date);

        $this->hraInstance    = $this->patient->surveyInstances->where('survey.name', Survey::HRA)->first();
        $this->vitalsInstance = $this->patient->surveyInstances->where('survey.name', Survey::VITALS)->first();

        $this->hraQuestions    = $this->hraInstance->questions;
        $this->vitalsQuestions = $this->vitalsInstance->questions;

        $this->hraAnswers    = $patient->answers->where('survey_instance_id', $this->hraInstance->id);
        $this->vitalsAnswers = $patient->answers->where('survey_instance_id', $this->vitalsInstance->id);

        //@todo:remove this after done testing
        $this->generateData($patient);
    }

    public function generateData($patient)
    {
        $patientPppData = $this->patient
            ->personalizedPreventionPlan()
            ->updateOrCreate(
                [
                    'patient_id' => $patient->id,
                ],
                [
                    'hra_answers'      => $this->hraAnswers,
                    'vitals_answers'   => $this->vitalsAnswers,
                    'answers_for_eval' => $this->getAnswersToEvaluate(),
                ]
            );

        return $patientPppData;
    }

    private function getAnswersToEvaluate()
    {
        $data = [];
        /*vitals*/
        $data['blood_pressure']       = $this->answerForVitalsQuestionWithOrder(1);
        $data['weight']               = $this->answerForVitalsQuestionWithOrder(2);
        $data['height']               = $this->answerForVitalsQuestionWithOrder(3);
        $data['bmi']                  = $this->answerForVitalsQuestionWithOrder(4);
        $data['cognitive_assessment'] = $this->answerForVitalsQuestionWithOrder(5, 'a');
        /*vitals*/
        /*HRA*/
        $data['race']                          = $this->answerForHraQuestionWithOrder(1);
        $data['age']                           = $this->answerForHraQuestionWithOrder(2);
        $data['sex']                           = $this->answerForHraQuestionWithOrder(4);
        $data['fruit_veggies']                 = $this->answerForHraQuestionWithOrder(6);
        $data['whole_grain']                   = $this->answerForHraQuestionWithOrder(7);
        $data['fatty_fried_foods']             = $this->answerForHraQuestionWithOrder(8);
        $data['candy_sugary_beverages']        = $this->answerForHraQuestionWithOrder(9);
        $data['current_smoker']                = $this->answerForHraQuestionWithOrder(11);
        $data['smoker_interested_quitting']    = $this->answerForHraQuestionWithOrder(11, 'd');
        $data['alcohol_use']                   = $this->answerForHraQuestionWithOrder(12, 'a');
        $data['recreational_drugs']            = $this->answerForHraQuestionWithOrder(13);
        $data['physical_activity']             = $this->answerForHraQuestionWithOrder(14);
        $data['sexually_active']               = $this->answerForHraQuestionWithOrder(15);
        $data['multiple_partners']             = $this->answerForHraQuestionWithOrder(15, 'a');
        $data['safe_sex']                      = $this->answerForHraQuestionWithOrder(15, 'b');
        $data['multipleQuestion16']            = $this->answerForHraQuestionWithOrder(16);
        $data['family_conditions']             = $this->answerForHraQuestionWithOrder(18);
        $data['family_members_with_condition'] = $this->answerForHraQuestionWithOrder(18, 'a');
        $data['emotional']                     = $this->answerForHraQuestionWithOrder(22, '1');
        $data['fall_risk']                     = $this->answerForHraQuestionWithOrder(24);
        $data['hearing_impairment']            = $this->answerForHraQuestionWithOrder(25);
        /*next two should be 26 & 26a according to the excel sheet*/
        $data['adl']                            = $this->answerForHraQuestionWithOrder(23);
        $data['assistance_in_daily_activities'] = $this->answerForHraQuestionWithOrder(23, 'a');
        $data['flu_influenza']                  = $this->answerForHraQuestionWithOrder(26);
        $data['tetanus_diphtheria']             = $this->answerForHraQuestionWithOrder(27);
        $data['chicken_pox']                    = $this->answerForHraQuestionWithOrder(29);
        $data['hepatitis_b']                    = $this->answerForHraQuestionWithOrder(30);
        $data['rubella']                        = $this->answerForHraQuestionWithOrder(31);
        $data['human_papillomavirus']           = $this->answerForHraQuestionWithOrder(32);
        $data['shingles']                       = $this->answerForHraQuestionWithOrder(33);
        $data['pneumococcal_vaccine']           = $this->answerForHraQuestionWithOrder(34);
        $data['breast_cancer_screening']        = $this->answerForHraQuestionWithOrder(35);
        $data['cervical_cancer_screening']      = $this->answerForHraQuestionWithOrder(36);
        $data['colorectal_cancer_screening']    = $this->answerForHraQuestionWithOrder(37);
        $data['prostate_cancer_screening']      = $this->answerForHraQuestionWithOrder(39);
        $data['glaukoma_screening']             = $this->answerForHraQuestionWithOrder(40);
        $data['osteoporosis_screening']         = $this->answerForHraQuestionWithOrder(41);
        $data['domestic_violence_screen']       = $this->answerForHraQuestionWithOrder(42);
        /*this should be OrderId 43 according to excel sheet*/
        $data['medical_attonery'] = $this->answerForHraQuestionWithOrder(44);
        /*this should be OrderId 44 according to excel sheet*/
        $data['living_will'] = $this->answerForHraQuestionWithOrder(45);

        return $data;
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
}


