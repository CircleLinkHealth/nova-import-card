<?php

namespace App\Services;


use App\Survey;
use App\SurveyInstance;
use App\User;
use Illuminate\Support\Collection;

class GeneratePersonalizedPreventionPlanService
{
    /**
     * @var \App\User
     */
    protected $patient;

    /**
     * @var SurveyInstance
     */
    protected $hraInstance;

    /**
     * @var SurveyInstance
     */
    protected $vitalsInstance;

    /**
     * @var Collection
     */
    protected $hraQuestions;

    /**
     * @var Collection
     */
    protected $vitalsQuestions;

    /**
     * @var Collection
     */
    protected $hraAnswers;

    /**
     * @var Collection
     */
    protected $vitalsAnswers;

    /**
     * GeneratePersonalizedPreventionPlanService constructor.
     *
     * @param User $patient
     * @param $date
     */
    public function __construct($patient)
    {
        $this->patient = $patient;

        $this->hraInstance    = $this->patient->surveyInstances->where('survey.name', Survey::HRA)->first();
        $this->vitalsInstance = $this->patient->surveyInstances->where('survey.name', Survey::VITALS)->first();

        $this->hraQuestions    = $this->hraInstance->questions;
        $this->vitalsQuestions = $this->vitalsInstance->questions;

        $this->hraAnswers    = $patient->answers->where('survey_instance_id', $this->hraInstance->id);
        $this->vitalsAnswers = $patient->answers->where('survey_instance_id', $this->vitalsInstance->id);

    }

    public function generateData()
    {
        return $this->patient
            ->personalizedPreventionPlan()
            ->updateOrCreate(
                [
                    'hra_instance_id'    => $this->hraInstance->id,
                    'vitals_instance_id' => $this->vitalsInstance->id,
                ],
                [
                    'hra_answers'      => $this->hraAnswers,
                    'vitals_answers'   => $this->vitalsAnswers,
                    'answers_for_eval' => $this->getAnswersToEvaluate(),
                ]
            );

    }

    private function getAnswersToEvaluate()
    {
        $answers = [
            /*vitals*/
            'blood_pressure'                 => $this->answerForVitalsQuestionWithOrder(1),
            'weight'                         => $this->answerForVitalsQuestionWithOrder(2),
            'height'                         => $this->answerForVitalsQuestionWithOrder(3),
            'bmi'                            => $this->answerForVitalsQuestionWithOrder(4),
            'cognitive_assessment'           => $this->answerForVitalsQuestionWithOrder(5, 'c'),
            /*vitals*/
            /*HRA*/
            'race'                           => $this->answerForHraQuestionWithOrder(1, 'a'),
            'age'                            => $this->answerForHraQuestionWithOrder(2),
            'sex'                            => $this->answerForHraQuestionWithOrder(4),
            'fruit_veggies'                  => $this->answerForHraQuestionWithOrder(6),
            'whole_grain'                    => $this->answerForHraQuestionWithOrder(7),
            'fatty_fried_foods'              => $this->answerForHraQuestionWithOrder(8),
            'candy_sugary_beverages'         => $this->answerForHraQuestionWithOrder(9),
            'current_smoker'                 => $this->answerForHraQuestionWithOrder(11),
            'smoker_interested_quitting'     => $this->answerForHraQuestionWithOrder(11, 'd'),
            'alcohol_use'                    => $this->answerForHraQuestionWithOrder(12, 'a'),
            'recreational_drugs'             => $this->answerForHraQuestionWithOrder(13),
            'physical_activity'              => $this->answerForHraQuestionWithOrder(14),
            'sexually_active'                => $this->answerForHraQuestionWithOrder(15),
            'multiple_partners'              => $this->answerForHraQuestionWithOrder(15, 'a'),
            'safe_sex'                       => $this->answerForHraQuestionWithOrder(15, 'b'),
            'multipleQuestion16'             => $this->answerForHraQuestionWithOrder(16),
            'family_conditions'              => $this->answerForHraQuestionWithOrder(18),
            'family_members_with_condition'  => $this->answerForHraQuestionWithOrder(18, 'a'),
            'emotional_little_interest'      => $this->answerForHraQuestionWithOrder(22, '1'),
            'emotional_depressed'            => $this->answerForHraQuestionWithOrder(22, '2'),
            'fall_risk'                      => $this->answerForHraQuestionWithOrder(24),
            'hearing_impairment'             => $this->answerForHraQuestionWithOrder(25),
            /*next two should be 26 & 26a according to the excel sheet*/
            'adl'                            => $this->answerForHraQuestionWithOrder(23),
            'assistance_in_daily_activities' => $this->answerForHraQuestionWithOrder(23, 'a'),
            'flu_influenza'                  => $this->answerForHraQuestionWithOrder(26),
            'tetanus_diphtheria'             => $this->answerForHraQuestionWithOrder(27),
            'chicken_pox'                    => $this->answerForHraQuestionWithOrder(29),
            'hepatitis_b'                    => $this->answerForHraQuestionWithOrder(30),
            'rubella'                        => $this->answerForHraQuestionWithOrder(31),
            'human_papillomavirus'           => $this->answerForHraQuestionWithOrder(32),
            'shingles'                       => $this->answerForHraQuestionWithOrder(33),
            'pneumococcal_vaccine'           => $this->answerForHraQuestionWithOrder(34),
            'breast_cancer_screening'        => $this->answerForHraQuestionWithOrder(35),
            'cervical_cancer_screening'      => $this->answerForHraQuestionWithOrder(36),
            'colorectal_cancer_screening'    => $this->answerForHraQuestionWithOrder(37),
            'prostate_cancer_screening'      => $this->answerForHraQuestionWithOrder(39),
            'glaukoma_screening'             => $this->answerForHraQuestionWithOrder(40),
            'osteoporosis_screening'         => $this->answerForHraQuestionWithOrder(41),
            'domestic_violence_screen'       => $this->answerForHraQuestionWithOrder(42),
            /*this should be OrderId 43 according to excel sheet*/
            'medical_attorney'               => $this->answerForHraQuestionWithOrder(44),
            /*this should be OrderId 44 according to excel sheet*/
            'living_will'                    => $this->answerForHraQuestionWithOrder(45),
        ];

        return $answers;
    }

    private function answerForVitalsQuestionWithOrder($order, $subOrder = null)
    {
        $question = $this->vitalsQuestions->where('pivot.order', $order)->where('pivot.sub_order', $subOrder)->first();

        $answer = $this->vitalsAnswers->where('question_id', $question->id)->first();

        return GenerateProviderReportService::sanitizedValue($answer);

    }

    private function answerForHraQuestionWithOrder($order, $subOrder = null)
    {
        $question = $this->hraQuestions->where('pivot.order', $order)->where('pivot.sub_order', $subOrder)->first();

        $answer = $this->hraAnswers->where('question_id', $question->id)->first();

        return GenerateProviderReportService::sanitizedValue($answer);
    }
}


