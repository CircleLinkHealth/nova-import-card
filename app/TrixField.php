<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrixField extends Model
{
    /**
     * The Type to get scripts for the Care Ambassadors Page.
     */
    const CARE_AMBASSADOR_SCRIPT = 'care_ambassador_script';

    protected $fillable = [
        'type',
        'language',
        'body',
    ];

    /**
     * Get Care.
     *
     * @param $builder
     * @param $language
     */
    public function scopeCareAmbassador($builder, $language)
    {
        $builder->where('type', TrixField::CARE_AMBASSADOR_SCRIPT)
            ->where('language', strtolower($language));
    }
}
