<?php

namespace App;

use CircleLinkHealth\Core\Entities\BaseModel;

/**
 * @property int id
 * @property string identifier
 * @property int survey_id
 * @property string body
 * @property bool optional
 * @property array|null conditions
 * @property int question_group_id
 * @property QuestionType type
 *
 * Class Question
 */
class Question extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'identifier',
        'survey_id',
        'body',
        'optional',
        'conditions',
        'question_group_id',
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
            'sub_order',
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

    public function questionGroup()
    {
        return $this->belongsTo(QuestionGroup::class, 'question_group_id');
    }

    public function scopeNotOptional($query)
    {
        $query->where('optional', false);
    }
}
