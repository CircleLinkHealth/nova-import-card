<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuestionType extends Model
{
    const CHECKBOX = 'checkbox';

    const TEXT = 'text';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'question_id',
        'answer_type',
    ];


    public function question(){
        return $this->belongsTo(Question::class, 'question_id');
    }

    public function possibleAnswers(){
        return $this->hasMany(QuestionTypesAnswers::class, 'question_type_id');
    }

}
