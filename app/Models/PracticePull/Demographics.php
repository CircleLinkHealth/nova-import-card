<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Models\PracticePull;

use CircleLinkHealth\Core\Entities\BaseModel;

class Demographics extends BaseModel
{
    protected $dates = [
        'dob', 'last_encounter',
    ];
    protected $fillable = [
        'mrn',
        'first_name',
        'last_name',
        'last_encounter',
        'dob',
        'gender',
        'lang',
        'referring_provider_name',
        'cell_phone',
        'home_phone',
        'other_phone',
        'primary_phone',
        'email',
        'street',
        'street2',
        'city',
        'state',
        'zip',
        'primary_insurance',
        'secondary_insurance',
        'tertiary_insurance',
        'location_id',
        'billing_provider_user_id',
        'practice_id',
    ];
    protected $table = 'practice_pull_demographics';
}
