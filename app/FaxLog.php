<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class FaxLog extends Model
{
    protected $casts = [
        'response' => 'array',
    ];
    protected $fillable = [
        'fax_id',
        'event_type',
        'status',
        'direction',
        'response',
    ];
}
