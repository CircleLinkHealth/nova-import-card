<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\TwilioIntegration\Models;

use CircleLinkHealth\Core\Entities\BaseModel;

class TwilioLookup extends BaseModel
{
    protected $casts = [
        'is_mobile' => 'boolean',
    ];

    protected $fillable = [
        'phone_number',
        'is_mobile',
        'carrier',
        'caller_name',
        'api_error_code',
        'api_error_details',
    ];
    protected $table = 'twilio_lookup_api';
}
