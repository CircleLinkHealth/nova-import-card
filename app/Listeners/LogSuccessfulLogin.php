<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use App\LoginLogout;
use Carbon\Carbon;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogSuccessfulLogin
{
    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param Login $event
     */
    public function handle(Login $event)
    {
        try {
            LoginLogout::create([
                'user_id'    => $event->user->id,
                'login_time' => Carbon::parse(now())->toDateTime(),
                'ip_address' => $this->request->ip(),
            ]);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }
    }
}
