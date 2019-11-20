<?php


namespace App\Services;


use App\SurveyInstance;
use App\User;

class SurveyAnswerSuggestionsCalculator
{
    /** @var User $patient */
    protected $patient;

    /** @var SurveyInstance $instance */
    protected $instance;

    public function __construct(User $patientWithSurvey, SurveyInstance $instance)
    {
        $this->patient  = $patientWithSurvey;
        $this->instance = $instance;
    }

    public function calculate()
    {
        $this->suggestAnswerForAge();
    }

    private function suggestAnswerForAge()
    {
        $target = $this->instance
            ->questions()
            ->wherePivot('order', '=', self::HRA_QUESTION_ORDERS['age']['order'])
            ->first();

        Answer::updateOrCreate([
            'user_id'            => $this->patient->id,
            'survey_instance_id' => $this->instance->id,
            'question_id'        => $target->id,
        ], [
            'suggested_value' => ["value" => [$this->patient->getAge()]],
        ]);
    }

    const HRA_QUESTION_ORDERS = [
        'age' => ['order' => 2, 'sub_order' => null],
    ];

}
