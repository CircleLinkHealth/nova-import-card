<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Models\PracticePull;

use CircleLinkHealth\Core\Entities\BaseModel;

class Problem extends BaseModel
{
    protected $dates = [
        'start', 'stop',
    ];
    protected $fillable = [
        'billing_provider_user_id',
        'location_id',
        'practice_id',
        'mrn',
        'name',
        'code',
        'code_type',
        'start',
        'stop',
        'status',
    ];
    protected $table = 'practice_pull_problems';
}
