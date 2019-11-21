<?php


namespace App\Services;


use App\Answer;
use App\SurveyInstance;
use App\User;
use Illuminate\Support\Facades\DB;

class SurveyAnswerSuggestionsCalculator
{
    /** @var User $patient */
    protected $patient;

    /** @var SurveyInstance $hraInstance */
    protected $hraInstance;

    /** @var SurveyInstance $vitalsInstance */
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

    private function suggestAnswerForRace()
    {

    }

    private function suggestAnswerForHispanicLatino()
    {

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
            'suggested_value' => ["value" => [$this->patient->getAge()]],
        ]);
    }

    private function suggestAnswerForHeight()
    {

    }

    private function suggestAnswerForSex()
    {

    }

    private function suggestAnswerForAlcohol()
    {

    }

    private function suggestAnswerForConditions()
    {

    }

    /**
     * TODO: blocking ticket: CPM-1753
     */
    private function suggestAnswerForMedications()
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

    private function suggestAnswerForWeight()
    {

    }

    /**
     * TODO: should get questions using a HUMAN-MADE IDENTIFIER, order can change at any time
     *       see ticket CPM-1508
     */
    const HRA_QUESTION_ORDERS = [
        'race'            => ['order' => 1, 'sub_order' => 'a'],
        'hispanic-latino' => ['order' => 1, 'sub_order' => 'b'],
        'age'             => ['order' => 2, 'sub_order' => null], //patient_info
        'height'          => ['order' => 3, 'sub_order' => null],
        'sex'             => ['order' => 4, 'sub_order' => null], //patient_info
        'alcohol'         => ['order' => 12, 'sub_order' => null],
        'conditions'      => ['order' => 16, 'sub_order' => null], //ccd_problems
        'medications'     => ['order' => 20, 'sub_order' => null], //ccd_medications
        'allergies'       => ['order' => 21, 'sub_order' => null], //ccd_allergies
    ];

    const VITALS_QUESTIONS_ORDER = [
        'weight' => ['order' => 2, 'sub_order' => null],
        'height' => ['order' => 3, 'sub_order' => null],
    ];

}
