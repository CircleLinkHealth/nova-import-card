<?php

namespace App;

use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\BaseModel;
use Illuminate\Support\Collection;

/**
 * @property int id
 * @property int survey_id
 * @property string year
 * @property Survey survey
 * @property Collection users
 * @property Collection questions
 *
 * Class SurveyInstance
 * @package App
 */
class SurveyInstance extends BaseModel
{
    const PENDING = 'pending';
    const IN_PROGRESS = 'in_progress';
    const COMPLETED = 'completed';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'survey_id',
        'year',
    ];

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

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'survey_questions', 'survey_instance_id',
            'question_id')
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

    public function scopeOfSurvey($query, $surveyName)
    {
        $query->whereHas('survey', function ($survey) use ($surveyName) {
            $survey->where('name', $surveyName);
        });

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
        $query->where('users_surveys.status', SurveyInstance::COMPLETED);
    }

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
            ? SurveyInstance::IN_PROGRESS
            : SurveyInstance::COMPLETED;
    }

    public function getNextUnansweredQuestion(User $user, $currentIndex = -1)
    {
        $newIndex = $currentIndex + 1;

        /** @var Question $nextQuestion */
        $nextQuestion = $this->questions->get($newIndex);
        if ( ! $nextQuestion) {
            return null;
        }


        // first check if we have an answer for this question
        // if we do, no need to make further checks
        // if we don't, let's check if we should have an answer

        $answer     = $this->getAnswerForQuestion($user, $nextQuestion->id);
        $isAnswered = $answer !== null;
        if ($isAnswered) {
            return $this->getNextUnansweredQuestion($user, $currentIndex + 1);
        }

        $isDisabled = false;
        if ( ! empty($nextQuestion->conditions)) {
            foreach ($nextQuestion->conditions as $condition) {
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
                    if ($condition['operator'] === 'greater_or_equal_than') {
                        //Again we use only the first Question of the related Questions, which is OK for now.
                        $isDisabled = ! ($firstQuestionAnswer->value['value'] >= $condition['related_question_expected_answer']);
                        break;
                    }

                    if ($condition['operator'] === 'less_or_equal_than') {
                        $isDisabled = ! ($firstQuestionAnswer->value['value'] <= $condition['related_question_expected_answer']);
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
                            } else if (is_string($firstQuestionAnswer->value['value']) && empty($firstQuestionAnswer->value['value'])) {
                                $isDisabled = true;
                            }
                        }
                    }
                }
            }
        }

        return $isDisabled
            ? $this->getNextUnansweredQuestion($user, $currentIndex + 1)
            : $nextQuestion;
    }

    private function getQuestionsOfOrder($order): Collection
    {
        return $this->questions->filter(function (Question $f) use ($order) {
            return $f->pivot->order === $order;
        });
    }

    private function getAnswerForQuestion(User $user, $questionId)
    {
        return $user->answers->firstWhere('question_id', '=', $questionId);
    }


}
