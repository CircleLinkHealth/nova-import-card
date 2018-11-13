<?php

namespace App;

/**
 * Enrollment Tips per Practice
 * @package App
 *
 * @property int practice_id
 * @property string content
 */
class PracticeEnrollmentTips extends BaseModel
{
    protected $fillable = [
        'practice_id',
        'content'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];
}
