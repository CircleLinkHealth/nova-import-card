<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'enrollment/sms/reply',
        '/sendgrid/status',
        '/postmark/status',
        '/postmark/inbound',
        '/twilio/sms/status',
        '/twilio/sms/inbound',
    ];
}
