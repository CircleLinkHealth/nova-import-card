<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

/**
 * Enrollment Tips per Practice.
 *
 * @property int practice_id
 * @property string content
 */
class PracticeEnrollmentTips extends BaseModel
{
    protected $dates = [
        'created_at',
        'updated_at',
    ];
    protected $fillable = [
        'practice_id',
        'content',
    ];
}
