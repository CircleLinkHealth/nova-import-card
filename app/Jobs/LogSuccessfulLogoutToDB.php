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
     */
    public function handle()
    {
        try {
            $authId = null !== $this->event->user->id ? $this->event->user->id : auth()->id();
            optional(
                LoginLogout::where([
                                       ['user_id', $authId],
                                       ['login_time', '<', now()],
                                       ['login_time', '>', now()->startOfDay()],
                                   ])->get()->last()
            )->update(['logout_time' => Carbon::parse(now())->toDateTime()]);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage()." authid:$authId");
        }
    }
}
