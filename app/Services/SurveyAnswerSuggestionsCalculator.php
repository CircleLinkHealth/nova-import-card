<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\Answer;
use App\SurveyInstance;
use App\User;
use Illuminate\Support\Facades\DB;

class SurveyAnswerSuggestionsCalculator
{
    /**
     * TODO: should get questions using a HUMAN-MADE IDENTIFIER, order can change at any time
     *       see ticket CPM-1508
     *  Raw data found in ccdas table
     *  BMI - ccda vitals
     *  find example ccdas in app-cpm-web/storage/ccdas/Samples.
     */
    const HRA_QUESTION_ORDERS = [
        'race'            => ['order' => 1, 'sub_order' => 'a'], //-> race and ethnicity report ccd_demographics_logs
        'hispanic-latino' => ['order' => 1, 'sub_order' => 'b'], //-> race and ethnicity report ccd_demographics_logs
        'age'             => ['order' => 2, 'sub_order' => null], //patient_info
        'height'          => ['order' => 3, 'sub_order' => null], //ccda - vitals section ?
        'sex'             => ['order' => 4, 'sub_order' => null], //patient_info
        'alcohol'         => ['order' => 12, 'sub_order' => null],
        'conditions'      => ['order' => 16, 'sub_order' => null], //ccd_problems
        'medications'     => ['order' => 20, 'sub_order' => null], //ccd_medications, ccd_instructions (?)
        'allergies'       => ['order' => 21, 'sub_order' => null], //ccd_allergies
    ];

    const VITALS_QUESTIONS_ORDER = [
        'weight' => ['order' => 2, 'sub_order' => null], //ccda - vitals
        'height' => ['order' => 3, 'sub_order' => null], //ccda - vitals
    ];

    /** @var SurveyInstance */
    protected $hraInstance;
    /** @var User */
    protected $patient;

    /** @var SurveyInstance */
    protected $vitalsInstance;

    public function __construct(User $patientWithSurvey, SurveyInstance $hraInstance, SurveyInstance $vitalsInstance)
    {
        $this->patient        = $patientWithSurvey;
        $this->hraInstance    = $hraInstance;
        $this->vitalsInstance = $vitalsInstance;
    }

    public function calculate()
    {
        $this->suggestAnswerForRace();
        $this->suggestAnswerForHispanicLatino();
        $this->suggestAnswerForAge();
        $this->suggestAnswerForHeight();
        $this->suggestAnswerForSex();
        $this->suggestAnswerForAlcohol();
        $this->suggestAnswerForConditions();
        $this->suggestAnswerForMedications();
        $this->suggestAnswerForAllergies();
        $this->suggestAnswerForWeight();
    }

    private function suggestAnswerForAge()
    {
        $target = $this->hraInstance
            ->questions()
            ->wherePivot('order', '=', self::HRA_QUESTION_ORDERS['age']['order'])
            ->first();

        Answer::updateOrCreate([
            'user_id'            => $this->patient->id,
            'survey_instance_id' => $this->hraInstance->id,
            'question_id'        => $target->id,
        ], [
            'suggested_value' => ['value' => [$this->patient->getAge()]],
        ]);
    }

    private function suggestAnswerForAlcohol()
    {
    }

    private function suggestAnswerForAllergies()
    {
        $target = $this->hraInstance
            ->questions()
            ->wherePivot('order', '=', self::HRA_QUESTION_ORDERS['allergies']['order'])
            ->first();

        //can't do $this->patient->ccdAllergies because we do not have the model in AWV
        $allergies = DB::table('ccd_allergies')->where('patient_id', '=', $this->patient->id)
            ->whereNull('deleted_at')
            ->get()
            ->map(function ($a) {
                return ['name' => $a->allergen_name];
            });

        Answer::updateOrCreate([
            'user_id'            => $this->patient->id,
            'survey_instance_id' => $this->hraInstance->id,
            'question_id'        => $target->id,
        ], [
            'suggested_value' => $allergies,
        ]);
    }

    private function suggestAnswerForConditions()
    {
    }

    private function suggestAnswerForHeight()
    {
    }

    private function suggestAnswerForHispanicLatino()
    {
    }

    /**
     * TODO: blocking ticket: CPM-1753.
     */
    private function suggestAnswerForMedications()
    {
    }

    private function suggestAnswerForRace()
    {
    }

    private function suggestAnswerForSex()
    {
    }

    private function suggestAnswerForWeight()
    {
    }
}
