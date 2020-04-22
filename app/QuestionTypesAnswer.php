<?php

namespace App;

use CircleLinkHealth\Core\Entities\BaseModel;

/**
 * @property int $id
 * @property int $question_type_id
 * @property string $value
 * @property array $options
 *
 * Class QuestionTypesAnswer
 */
class QuestionTypesAnswer extends BaseModel
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
