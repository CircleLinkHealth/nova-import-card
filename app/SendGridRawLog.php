<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Core\Entities\BaseModel;

class SendGridRawLog extends BaseModel
{
    protected $casts = [
        'event' => 'array',
    ];

    protected $fillable = [
        'events',
    ];
    protected $table = 'sendgrid_raw_logs';
}
