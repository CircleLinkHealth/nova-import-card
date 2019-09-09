<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use App\LoginLogout;
use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Request;

class LogSuccessfulLogout
{
    const LOGOUT = 'logout';
    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param Logout $event
     */
    public function handle(Logout $event)
    {
        LoginLogout::create([
            'user_id'    => $event->user->id,
            'event'      => self::LOGOUT,
            'ip_address' => $this->request->ip(),
        ]);
    }
}
