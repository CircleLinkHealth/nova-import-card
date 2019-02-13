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
        'survey_id',
        'survey_instance_id',
        'question_id',
        'question_answer_id',
        'value',
    ];
}
