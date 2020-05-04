<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Core\Entities\BaseModel;

class QuestionType extends BaseModel
{
    const ADDRESS      = 'address';
    const CHECKBOX     = 'checkbox';
    const CONFIRMATION = 'confirmation';

    const DATE = 'date';

    // Enrollmen Survey
    const DOB = 'dob';

    const MULTI_SELECT = 'multi_select';

    const NUMBER = 'number';
    const PHONE  = 'phone';

    const RADIO = 'radio';

    const SELECT = 'select';

    const TEXT = 'text';
    const TIME = 'time';
    // Enrollmen Survey end

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'question_id',
        'type',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }

    public function questionTypeAnswers()
    {
        return $this->hasMany(QuestionTypesAnswer::class, 'question_type_id');
    }
}
