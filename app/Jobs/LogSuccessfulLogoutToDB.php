<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\LoginLogout;
use Carbon\Carbon;
use Illuminate\Auth\Events\Logout;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LogSuccessfulLogoutToDB implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    /**
     * @var Logout
     */
    private $event;

    /**
     * Create a new job instance.
     *
     * @param Logout $event
     */
    public function __construct(Logout $event)
    {
        $this->event = $event;
    }

    /**
     * Execute the job.
     *
     */
    public function handle()
    {
        //In case of browser close will miss the logout event
        //in case of inactivity logout we dont have the $event->user (checking on it)
        try {
            $authId = null !== $this->event->user->id ? $this->event->user->id : auth()->id();
            LoginLogout::where([
                ['user_id', $authId],
                ['login_time', '<', $this->loginDateTime()],
                ['login_time', '>', $this->logoutDateTime()],
            ])->get()->last()->update(['logout_time' => Carbon::parse(now())->toDateTime()]);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }
    }

    /**
     * @return \DateTime
     */
    public function loginDateTime()
    {
        return Carbon::parse(now())->toDateTime();
    }

    /**
     * @return \DateTime
     */
    public function logoutDateTime()
    {
        return Carbon::parse(now())->startOfDay()->toDateTime();
    }
}
