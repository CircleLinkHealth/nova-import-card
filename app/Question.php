<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'survey_id',
        'body',
        'optional',
        'conditions'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'conditions' => 'array',
    ];

    public function surveyInstance()
    {
        return $this->belongsToMany(SurveyInstance::class, 'survey_questions', 'question_id',
            'survey_instance_id')->withPivot([
            'order',
        ]);
    }

    public function survey()
    {
        return $this->belongsTo(Survey::class, 'survey_id');
    }

    public function type()
    {
        return $this->hasOne(QuestionType::class, 'question_id', 'id');
    }

    public function scopeNotOptional($query){
        $query->where('optional', false);
    }
}
