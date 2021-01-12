<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\HraQuestionIdentifier;
use App\User;
use App\VitalsQuestionIdentifier;

class GeneratePersonalizedPreventionPlanService extends GenerateReportService
{
    /**
     * GeneratePersonalizedPreventionPlanService constructor.
     */
    public function __construct(User $patient)
    {
        parent::__construct($patient);
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
        return [
            // vitals
            'blood_pressure'       => $this->answerForVitalsQuestionWithIdentifier(VitalsQuestionIdentifier::BLOOD_PRESSURE),
            'weight'               => $this->answerForVitalsQuestionWithIdentifier(VitalsQuestionIdentifier::WEIGHT),
            'height'               => $this->answerForVitalsQuestionWithIdentifier(VitalsQuestionIdentifier::HEIGHT),
            'bmi'                  => $this->answerForVitalsQuestionWithIdentifier(VitalsQuestionIdentifier::BMI),
            'cognitive_assessment' => $this->answerForVitalsQuestionWithIdentifier(VitalsQuestionIdentifier::TOTAL_SCORE),
            // vitals
            // HRA
            'race'                          => $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::RACE),
            'age'                           => $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::AGE),
            'sex'                           => $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::SEX),
            'fruit_veggies'                 => $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::FRUIT),
            'whole_grain'                   => $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::FIBER),
            'fatty_fried_foods'             => $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::FATTY_FOOD),
            'candy_sugary_beverages'        => $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::SUGAR),
            'current_smoker'                => $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::TOBACCO),
            'smoker_interested_quitting'    => $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::TOBACCO_QUIT),
            'alcohol_use'                   => $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::ALCOHOL_CONSUMPTION),
            'recreational_drugs'            => $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::RECREATIONAL_DRUGS),
            'physical_activity'             => $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::EXERCISE),
            'sexually_active'               => $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::SEXUALLY_ACTIVE),
            'multiple_partners'             => $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::SEXUALLY_ACTIVE_PARTNERS),
            'safe_sex'                      => $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::SEXUALLY_ACTIVE_SAFE),
            'multipleQuestion16'            => $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::CONDITIONS),
            'family_conditions'             => $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::CONDITIONS_FAMILY),
            'family_members_with_condition' => $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::CONDITIONS_FAMILY_WHO),
            'emotional_little_interest'     => $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::INTEREST_DOING_THINGS),
            'emotional_depressed'           => $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::DEPRESSED),
            'fall_risk'                     => $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::FALL_INCIDENT),
            'hearing_impairment'            => $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::HEARING),
            // next two should be 26 & 26a according to the excel sheet
            'adl'                            => $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::DIFFICULTIES),
            'assistance_in_daily_activities' => $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::DIFFICULTIES_ASSISTANCE),
            'flu_influenza'                  => $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::FLU_SHOT),
            'tetanus_diphtheria'             => $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::TETANUS_VACCINATION),
            'chicken_pox'                    => $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::VARICELLA_VACCINATION),
            'hepatitis_b'                    => $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::HEPATITIS_B_VACCINATION),
            'rubella'                        => $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::MEASLES_VACCINATION),
            'human_papillomavirus'           => $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::PAPILLOMAVIRUS_VACCINATION),
            'shingles'                       => $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::RZV_ZVL),
            'pneumococcal_vaccine'           => $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::PCV13_PPSV23),
            'breast_cancer_screening'        => $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::MAMMOGRAM),
            'cervical_cancer_screening'      => $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::PAP_SMEAR),
            'colorectal_cancer_screening'    => $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::COLORECTAR_CANCER),
            'prostate_cancer_screening'      => $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::PROSTATE_CANCER),
            'glaukoma_screening'             => $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::GLAUCOMA),
            'osteoporosis_screening'         => $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::OSTEOPOROSIS),
            'domestic_violence_screen'       => $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::INTIMATE_PARTNER_VIOLENCE),
            // this should be OrderId 43 according to excel sheet
            'medical_attorney' => $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::MEDICAL_ATTORNEY),
            // this should be OrderId 44 according to excel sheet
            'living_will' => $this->answerForHraQuestionWithIdentifier(HraQuestionIdentifier::LIVING_WILL),
        ];
    }
}
