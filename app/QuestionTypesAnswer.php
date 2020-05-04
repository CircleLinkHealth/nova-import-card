<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Core\Entities\BaseModel;

/**
 * @property int    $id
 * @property int    $question_type_id
 * @property string $value
 * @property array  $options
 *
 * Class QuestionTypesAnswer
 */
class QuestionTypesAnswer extends BaseModel
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'options' => 'array',
    ];
    /*
     *
     * The attributes that are mass assignable.
     *
     * @var array
     *
     */
    protected $fillable = [
        'question_type_id',
        'value',
        'options',
    ];

    public function questionType()
    {
        return $this->belongsTo(QuestionType::class, 'question_type_id');
    }
}
