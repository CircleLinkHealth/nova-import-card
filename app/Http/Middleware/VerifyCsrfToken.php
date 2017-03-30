<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{

    protected $except = [

        'enrollment/sms/reply',
        '/twilio/token',
        '/twilio/call/make',
        '/admin/reports/monthly-billing/v2/markApproved',
        '/admin/reports/monthly-billing/v2/markRejected',
        '/admin/reports/monthly-billing/v2/data'

    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     *
     * @return mixed
     */
    public function handle(
        $request,
        Closure $next
    ) {
        return parent::handle($request, $next);
    }

}
