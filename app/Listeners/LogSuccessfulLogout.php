<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use App\LoginLogout;
use Carbon\Carbon;
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
    {
        //In case of browser close will miss the logout event
        //in case of inactivity logout we dont have the $event->user
        try {
            $authId = null !== $event->user->id ? $event->user->id : auth()->id();
            LoginLogout::where([
                ['user_id', $authId],
                ['login_time', '<', Carbon::parse(now())->toDateTime()],
            ])->get()->last()->update(['logout_time' => Carbon::parse(now())->toDateTime()]);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }
    }
}
