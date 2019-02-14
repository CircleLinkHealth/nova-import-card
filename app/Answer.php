<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
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
        'value_1',
        'value_2',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function surveyInstance()
    {
        return $this->belongsTo(SurveyInstance::class, 'survey_instance_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }

    public function questionTypesAnswer()
    {
        return $this->belongsTo(QuestionTypesAnswer::class, 'question_type_answer_id');
    }
}
