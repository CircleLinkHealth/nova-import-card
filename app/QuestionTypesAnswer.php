<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuestionTypesAnswer extends Model
{
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

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'options' => 'array',
    ];

    public function questionType()
    {
        return $this->belongsTo(QuestionType::class, 'question_type_id');
    }
}
