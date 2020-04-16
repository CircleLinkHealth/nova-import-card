<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdentifierFieldInQuestionsTable extends Migration
{
    private const HRA_SURVEY_NAME    = 'HRA';
    private const VITALS_SURVEY_NAME = 'Vitals';

    /** @var \Illuminate\Database\Query\Builder $hraQuestions */
    private $hraQuestions;

    private $hraQuestionsMap = [
        'Q_RACE'                       => ['order' => 1, 'sub_order' => 'a'],
        'Q_ETHNICITY'                  => ['order' => 1, 'sub_order' => 'b'],
        'Q_AGE'                        => ['order' => 2, 'sub_order' => null],
        'Q_HEIGHT'                     => ['order' => 3, 'sub_order' => null],
        'Q_SEX'                        => ['order' => 4, 'sub_order' => null],
        'Q_RATE_HEALTH'                => ['order' => 5, 'sub_order' => null],
        'Q_FRUIT'                      => ['order' => 6, 'sub_order' => null],
        'Q_FIBER'                      => ['order' => 7, 'sub_order' => null],
        'Q_FATTY_FOOD'                 => ['order' => 8, 'sub_order' => null],
        'Q_SUGAR'                      => ['order' => 9, 'sub_order' => null],
        'Q_APPETITE'                   => ['order' => 10, 'sub_order' => null],
        'Q_TOBACCO'                    => ['order' => 11, 'sub_order' => null],
        'Q_TOBACCO_YEARS'              => ['order' => 11, 'sub_order' => 'a'],
        'Q_TOBACCO_LAST_TIME'          => ['order' => 11, 'sub_order' => 'b'],
        'Q_TOBACCO_PACKS'              => ['order' => 11, 'sub_order' => 'c'],
        'Q_TOBACCO_QUIT'               => ['order' => 11, 'sub_order' => 'd'],
        'Q_ALCOHOL'                    => ['order' => 12, 'sub_order' => null],
        'Q_ALCOHOL_CONSUMPTION'        => ['order' => 12, 'sub_order' => 'a'],
        'Q_RECREATIONAL_DRUGS'         => ['order' => 13, 'sub_order' => null],
        'Q_RECREATIONAL_DRUGS_WHICH'   => ['order' => 13, 'sub_order' => 'a'],
        'Q_EXERCISE'                   => ['order' => 14, 'sub_order' => null],
        'Q_SEXUALLY_ACTIVE'            => ['order' => 15, 'sub_order' => null],
        'Q_SEXUALLY_ACTIVE_PARTNERS'   => ['order' => 15, 'sub_order' => 'a'],
        'Q_SEXUALLY_ACTIVE_SAFE'       => ['order' => 15, 'sub_order' => 'b'],
        'Q_CONDITIONS'                 => ['order' => 16, 'sub_order' => null],
        'Q_CONDITIONS_EXTRA'           => ['order' => 17, 'sub_order' => null],
        'Q_CONDITIONS_FAMILY'          => ['order' => 18, 'sub_order' => null],
        'Q_CONDITIONS_FAMILY_WHO'      => ['order' => 18, 'sub_order' => 'a'],
        'Q_SURGERIES'                  => ['order' => 19, 'sub_order' => null],
        'Q_MEDICATION'                 => ['order' => 20, 'sub_order' => null],
        'Q_ALLERGIES'                  => ['order' => 21, 'sub_order' => null],
        'Q_INTEREST_DOING_THINGS'      => ['order' => 22, 'sub_order' => '1'],
        'Q_DEPRESSED'                  => ['order' => 22, 'sub_order' => '2'],
        'Q_DIFFICULTIES'               => ['order' => 23, 'sub_order' => null],
        'Q_DIFFICULTIES_ASSISTANCE'    => ['order' => 23, 'sub_order' => 'a'],
        'Q_FALL_INCIDENT'              => ['order' => 24, 'sub_order' => null],
        'Q_HEARING'                    => ['order' => 25, 'sub_order' => null],
        'Q_FLU_SHOT'                   => ['order' => 26, 'sub_order' => null],
        'Q_TDAP_VACCINATION'           => ['order' => 27, 'sub_order' => null],
        'Q_TETANUS_VACCINATION'        => ['order' => 28, 'sub_order' => null],
        'Q_VARICELLA_VACCINATION'      => ['order' => 29, 'sub_order' => null],
        'Q_HEPATITIS_B_VACCINATION'    => ['order' => 30, 'sub_order' => null],
        'Q_MEASLES_VACCINATION'        => ['order' => 31, 'sub_order' => null],
        'Q_PAPILLOMAVIRUS_VACCINATION' => ['order' => 32, 'sub_order' => null],
        'Q_RZV_ZVL'                    => ['order' => 33, 'sub_order' => null],
        'Q_PCV13_PPSV23'               => ['order' => 34, 'sub_order' => null],
        'Q_MAMMOGRAM'                  => ['order' => 35, 'sub_order' => null],
        'Q_PAP_SMEAR'                  => ['order' => 36, 'sub_order' => null],
        'Q_COLORECTAR_CANCER'          => ['order' => 37, 'sub_order' => null],
        'Q_SKIN_CANCER'                => ['order' => 38, 'sub_order' => null],
        'Q_PROSTATE_CANCER'            => ['order' => 39, 'sub_order' => null],
        'Q_GLAUCOMA'                   => ['order' => 40, 'sub_order' => null],
        'Q_OSTEOPOROSIS'               => ['order' => 41, 'sub_order' => null],
        'Q_INTIMATE_PARTNER_VIOLENCE'  => ['order' => 42, 'sub_order' => null],
        'Q_PHYSICIANS'                 => ['order' => 43, 'sub_order' => null],
        'Q_MEDICAL_ATTORNEY'           => ['order' => 44, 'sub_order' => null],
        'Q_LIVING_WILL'                => ['order' => 45, 'sub_order' => null],
        'Q_LIVING_WILL_AT_DOCTOR'      => ['order' => 45, 'sub_order' => 'a'],
        'Q_COMMENTS'                   => ['order' => 46, 'sub_order' => null],
    ];

    /** @var \Illuminate\Support\Collection $vitalsQuestions */
    private $vitalsQuestions;

    private $vitalsQuestionsMap = [
        'Q_BLOOD_PRESSURE' => ['order' => 1, 'sub_order' => null],
        'Q_WEIGHT'         => ['order' => 2, 'sub_order' => null],
        'Q_HEIGHT'         => ['order' => 3, 'sub_order' => null],
        'Q_BMI'            => ['order' => 4, 'sub_order' => null],
        'Q_WORD_RECALL'    => ['order' => 5, 'sub_order' => 'a'],
        'Q_CLOCK_DRAW'     => ['order' => 5, 'sub_order' => 'b'],
        'Q_TOTAL_SCORE'    => ['order' => 5, 'sub_order' => 'c'],
    ];

    private function setup($surveyName)
    {
        $survey = DB::table('surveys')
            ->where('name', '=', $surveyName)
            ->first();

        if ( ! $survey) {
            return;
        }

        $surveyInstance = DB::table('survey_instances')
            ->where('survey_id', '=', $survey->id)
            ->orderByDesc('year')
            ->first();

        if ( ! $surveyInstance) {
            return;
        }

        if (self::HRA_SURVEY_NAME === $surveyName) {
            $variable = 'hraQuestions';
        } else {
            $variable = 'vitalsQuestions';
        }

        $this->$variable = DB::table('questions')
            ->join('survey_questions', 'survey_questions.question_id', '=', 'questions.id')
            ->where('survey_questions.survey_instance_id', '=', $surveyInstance->id)
            ->get();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('questions', 'identifier')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->removeColumn('identifier');
            });
        }
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! Schema::hasColumn('questions', 'identifier')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->string('identifier')->after('id')->nullable(true);
            });
        }

        $this->setup(self::HRA_SURVEY_NAME);
        $this->setup(self::VITALS_SURVEY_NAME);

        //nothing to do, survey not found
        if ( ! $this->hraQuestions || 0 === $this->hraQuestions->count()) {
            return;
        }

        foreach ($this->hraQuestionsMap as $key => $value) {
            $this->setIdentifierForQuestion(self::HRA_SURVEY_NAME, $key, $value['order'], $value['sub_order']);
        }

        foreach ($this->vitalsQuestionsMap as $key => $value) {
            $this->setIdentifierForQuestion(self::VITALS_SURVEY_NAME, $key, $value['order'], $value['sub_order']);
        }
    }

    private function getQuestionOfOrder($surveyName, $order, $subOrder = null)
    {
        if (self::HRA_SURVEY_NAME === $surveyName) {
            $variable = 'hraQuestions';
        } else {
            $variable = 'vitalsQuestions';
        }

        return $this->$variable->where('order', '=', $order)
            ->when($subOrder, function ($q) use ($subOrder) {
                                   return $q->where('sub_order', '=', $subOrder);
                               })
            ->first();
    }

    private function setIdentifierForQuestion($surveyName, $identifier, $order, $subOrder = null)
    {
        $question = $this->getQuestionOfOrder($surveyName, $order, $subOrder);

        if ( ! $question) {
            return;
        }

        DB::table('questions')
            ->where('id', '=', $question->id)
            ->update(['identifier' => $identifier]);
    }
}
