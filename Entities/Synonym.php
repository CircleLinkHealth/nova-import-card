<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Synonyms\Entities;

use Illuminate\Database\Eloquent\Model;

class Synonym extends Model
{
    protected $fillable = [
        'synonymable_type',
        'synonymable_id',
        'column',
        'synonym',
    ];

    /**
     * Get the owning synonymable model.
     */
    public function synonymable()
    {
        return $this->morphTo();
    }
}
