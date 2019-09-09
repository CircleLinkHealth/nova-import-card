<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use App\LoginLogout;
use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
    {//@todo: not really sure whats happening here yet...
        //1. Will break if user is logged in in two browsers (and has to logged out of one)
        //2.In case of browser close will miss the logout event

        //in case of inactivity logout we dont have the $event->user
        $authId = null !== $event->user->id ? $event->user->id : auth()->id();

        try {
            LoginLogout::create([
                'user_id'    => $authId,
                'event'      => self::LOGOUT,
                'ip_address' => $this->request->ip(),
            ]);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }
    }
}
