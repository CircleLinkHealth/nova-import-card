<?php

namespace App;

use CircleLinkHealth\Core\Entities\BaseModel;

class QuestionType extends BaseModel
{
    const CHECKBOX = 'checkbox';

    const TEXT = 'text';

    const RADIO = 'radio';

    const NUMBER = 'number';

    const DATE = 'date';

    const SELECT = 'select';

    const MULTI_SELECT = 'multi_select';

    // Enrollmen Survey
    const DOB = 'dob';
    const PHONE = 'phone';
    const ADDRESS = 'address';
    const TIME = 'time';
    const CONFIRMATION = 'confirmation';
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
