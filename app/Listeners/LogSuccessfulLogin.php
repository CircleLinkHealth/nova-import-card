<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use App\LoginLogout;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;

class LogSuccessfulLogin
{
    const LOGIN = 'login';
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
        LoginLogout::create([
            'user_id'    => $event->user->id,
            'event'      => self::LOGIN,
            'ip_address' => $this->request->ip(),
        ]);
    }
}
