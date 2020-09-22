<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * Indicates whether the XSRF-TOKEN cookie should be set on the response.
     *
     * @var bool
     */
    protected $addHttpCookie = true;
    
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
        '/admin/reports/monthly-billing/v2/updateApproved',
        '/admin/reports/monthly-billing/v2/updateRejected',
        '/admin/reports/monthly-billing/v2/data',
        '/admin/reports/monthly-billing/v2/storeProblem',
        '/admin/reports/monthly-billing/v2/counts',
        'api/v2.1/pagetimer',
        'api/v2.1/time/patients',
        'webhooks/on-sent-fax',
    ];
}
