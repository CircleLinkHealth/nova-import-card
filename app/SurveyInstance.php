<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\BaseModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * @property int id
 * @property int survey_id
 * @property string year
 * @property Survey survey
 * @property Collection users
 * @property Collection questions
 *
 * Class SurveyInstance
 */
class SurveyInstance extends BaseModel
{
    const COMPLETED   = 'completed';
    const IN_PROGRESS = 'in_progress';
    const PENDING     = 'pending';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'survey_id',
        'year',
    ];

    public function calculateCurrentStatusForUser(User $user)
    {
        $this->loadMissing('questions');
        $user->loadMissing([
            'answers' => function ($answer) {
                $answer->whereHas('surveyInstance', function ($instance) {
                    $instance->where('id', $this->id);
                });
            },
        ]);

        $next = $this->getNextUnansweredQuestion($user);

        return $next
            ? ['status' => self::IN_PROGRESS, 'next_question_id' => $next->id]
            : ['status' => self::COMPLETED, 'next_question_id' => null];
    }

    public function getNextUnansweredQuestion(User $user, $currentIndex = -1, $skipOptionals = true)
    {
        $newIndex = $currentIndex + 1;

        /** @var Question $nextQuestion */
        $nextQuestion = $this->questions->get($newIndex);
        if ( ! $nextQuestion) {
            return;
        }

        if ($user->hasRole('survey-only')
            && $nextQuestion->optional
            && ! empty($nextQuestion->conditions)
            && array_key_exists('nonAwvCheck', $nextQuestion->conditions[0])) {
            $answer = $this->getAnswerForQuestion($user, $nextQuestion->id);

            return empty($answer)
                ? $nextQuestion
                : null;
        }

        // 1. first check if the question is optional
        // 2. then check if we have an answer for this question
        // 2.1 if we do, no need to make further checks
        // 2.2 if we don't, let's check if we should have an answer

        if ($skipOptionals && $nextQuestion->optional) {
            return $this->getNextUnansweredQuestion($user, $newIndex, $skipOptionals);
        }

        $answer     = $this->getAnswerForQuestion($user, $nextQuestion->id);
        $isAnswered = null !== $answer;
        if ($isAnswered) {
            return $this->getNextUnansweredQuestion($user, $newIndex, $skipOptionals);
        }

        $isDisabled = false;
        if ( ! empty($nextQuestion->conditions)) {
            foreach ($nextQuestion->conditions as $condition) {
                if ( ! isset($condition['related_question_order_number'])) {
                    continue;
                }

                $questionsOfOrder = $this->getQuestionsOfOrder($condition['related_question_order_number']);
                //we are evaluating only the first condition.related_question_order_number
                //For now is OK since we are depending only on ONE related Question
                /** @var Question $firstQuestion */
                $firstQuestion = $questionsOfOrder->first();

                /** @var Answer $firstQuestionAnswer */
                $firstQuestionAnswer = $this->getAnswerForQuestion($user, $firstQuestion->id);
                if ( ! $firstQuestionAnswer) {
                    $isDisabled = true;
                    break;
                }

                //If conditions needs to be compared against to "gte" or "lte"
                if (isset($condition['operator'])) {
                    $valueToCheck = $firstQuestionAnswer->value['value'];
                    $valueToCheck = $this->getValue($valueToCheck);

                    if ('greater_or_equal_than' === $condition['operator']) {
                        //Again we use only the first Question of the related Questions, which is OK for now.
                        $isDisabled = ! ($valueToCheck >= $condition['related_question_expected_answer']);
                        break;
                    }

                    if ('less_or_equal_than' === $condition['operator']) {
                        $isDisabled = ! ($valueToCheck <= $condition['related_question_expected_answer']);
                        break;
                    }
                }

                if (isset($condition['related_question_expected_answer'])) {
                    $filtered = collect($nextQuestion->conditions)
                        ->filter(function ($q) use ($firstQuestion, $firstQuestionAnswer) {
                            return $q['related_question_order_number'] === $firstQuestion->pivot->order && $q['related_question_expected_answer'] === $firstQuestionAnswer->value['value'];
                        });

                    if ($filtered->isEmpty()) {
                        $isDisabled = true;
                        break;
                    }
                } else {
                    //we are looking for any answer
                    if ( ! isset($firstQuestionAnswer->value['value'])) {
                        if (is_array($firstQuestionAnswer->value) && empty($firstQuestionAnswer->value)) {
                            $isDisabled = true;
                        }
                    } else {
                        if (isset($firstQuestionAnswer->value['value']['value'])) {
                            if (empty($firstQuestionAnswer->value['value']['value'])) {
                                $isDisabled = true;
                            }
                        } else {
                            if (is_array($firstQuestionAnswer->value['value']) && empty($firstQuestionAnswer->value['value'])) {
                                $isDisabled = true;
                            } elseif (is_string($firstQuestionAnswer->value['value']) && empty($firstQuestionAnswer->value['value'])) {
                                $isDisabled = true;
                            }
                        }
                    }
                }
            }
        }

        return $isDisabled
            ? $this->getNextUnansweredQuestion($user, $newIndex, $skipOptionals)
            : $nextQuestion;
    }

    public function questions()
    {
        return $this->belongsToMany(
            Question::class,
            'survey_questions',
            'survey_instance_id',
            'question_id'
        )
            ->withPivot([
                'order',
                'sub_order',
            ])
            ->orderBy('pivot_order')
            ->orderBy('pivot_sub_order');
    }

    public function scopeCurrent($query)
    {
        $query->where('year', Carbon::now()->year);
    }

    public function scopeForYear($query, $year)
    {
        if (is_a($year, Carbon::class)) {
            $year = $year->year;
        }

        $query->where('year', $year);
    }

    public function scopeIsCompletedForPatient($query)
    {
        $query->where('users_surveys.status', self::COMPLETED);
    }

    /**
     * Scope most recent per survey id.
     */
    public function scopeMostRecent(Builder $query)
    {
        /**
         * The raw query:
         * SELECT survey_instances.*
         * FROM survey_instances INNER JOIN (
         * # this inner join creates a table with a field of the survey id and another field of the list of years
         * # the list of years is ordered by most recent year (i.e. 2019, 2018, 2017)
         * SELECT survey_id, GROUP_CONCAT(year order by year desc) grouped_year
         * FROM survey_instances
         * GROUP BY survey_id) group_max
         * ON survey_instances.survey_id = group_max.survey_id
         * # FIND_IN_SET is called to find the year in first of position of the list of years (which means the most recent)
         * AND FIND_IN_SET(year, grouped_year) = 1
         * ORDER BY survey_instances.year DESC;.
         */
        $table = $this->getTable();
        $query->join(
            DB::raw("
                    (SELECT
                      survey_id,
                      GROUP_CONCAT(year order by year desc) grouped_year
                    FROM
                      $table
                    GROUP BY survey_id) group_max"),
            "$table.survey_id",
            '=',
            'group_max.survey_id'
        )
            ->whereRaw('FIND_IN_SET(year, grouped_year) = 1')
            ->orderByDesc("$table.year");
    }

    public function scopeOfSurvey($query, $surveyName)
    {
        $query->whereHas('survey', function ($survey) use ($surveyName) {
            $survey->where('name', $surveyName);
        });
    }

    public function survey()
    {
        return $this->belongsTo(Survey::class, 'survey_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'users_surveys', 'survey_instance_id', 'user_id')
            ->withPivot([
                'survey_id',
                'last_question_answered_id',
                'status',
                'start_date',
                'completed_at',
            ])
            ->withTimestamps();
    }

    private function getAnswerForQuestion(User $user, $questionId)
    {
        return $user->answers()
            ->where('survey_instance_id', $this->id)
            ->where('question_id', $questionId)
            ->first();
    }

    private function getQuestionsOfOrder($order): Collection
    {
        return $this->questions->filter(function (Question $f) use ($order) {
            return $f->pivot->order === $order;
        });
    }

    private function getValue($val)
    {
        if (is_array($val)) {
            return $this->getValue(reset($val));
        }

        return $val;
    }
}
