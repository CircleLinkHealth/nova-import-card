<?php

namespace App;

use CircleLinkHealth\Core\Entities\BaseModel;

class SurveyQuestion extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'survey_instance_id',
        'question_id',
        'order',
        'sub_order',
    ];

    public function surveyInstance()
    {
        return $this->hasOne(SurveyInstance::class, 'survey_instance_id');
    }

    public function question()
    {
        return $this->hasOne(Question::class, 'question_id');
    }
}
