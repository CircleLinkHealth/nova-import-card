<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Question  extends \CircleLinkHealth\Core\Entities\BaseModel
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
        'conditions',
        'question_group_id'
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
            'sub_order'
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

    public function questionGroup(){
        return $this->belongsTo(QuestionGroup::class, 'question_group_id');
    }

    public function scopeNotOptional($query){
        $query->where('optional', false);
    }

}
