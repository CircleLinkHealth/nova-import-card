<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Models\PracticePull;

use CircleLinkHealth\Core\Entities\BaseModel;

class Allergy extends BaseModel
{
    protected $fillable = [
        'billing_provider_user_id',
        'location_id',
        'practice_id',
        'mrn',
        'name',
    ];
    protected $table = 'practice_pull_allergies';
}
