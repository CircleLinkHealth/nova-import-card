<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Core\Entities\BaseModel;

/**
 * @property string $user_id
 * @property int    $survey_instance_id
 * @property int    $question_id
 * @property int    $question_type_answer_id
 * @property array  $value
 * @property array  $suggested_value
 *
 * Class Answer
 */
class Answer extends BaseModel
{
    protected $casts = [
        'value'           => 'array',
        'suggested_value' => 'array',
    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'survey_instance_id',
        'question_id',
        'question_type_answer_id',
        'value',
        'suggested_value',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }

    public function questionTypesAnswer()
    {
        return $this->belongsTo(QuestionTypesAnswer::class, 'question_type_answer_id');
    }

    public function surveyInstance()
    {
        return $this->belongsTo(SurveyInstance::class, 'survey_instance_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
