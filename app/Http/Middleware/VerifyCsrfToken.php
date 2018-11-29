<?php

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
        '/twilio/call/place',
        '/twilio/call/status',
        '/admin/reports/monthly-billing/v2/updateApproved',
        '/admin/reports/monthly-billing/v2/updateRejected',
        '/admin/reports/monthly-billing/v2/data',
        '/admin/reports/monthly-billing/v2/storeProblem',
        '/admin/reports/monthly-billing/v2/counts',
        'api/v2.1/pagetimer'
    ];
}
