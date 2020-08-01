<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\LoginLogout;
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
        $authId = $this->event->user->id ?? null;
        try {
            $openSession = LoginLogout::where([
                ['user_id', $authId],
                ['login_time', '<', now()],
                ['login_time', '>', now()->startOfDay()],
            ])->orderByDesc('id')->first();

            if ($openSession) {
                $openSession->logout_time     = now();
                $openSession->duration_in_sec = $openSession->logout_time->diffInSeconds($openSession->login_time);
                $openSession->save();
            }
        } catch (\Exception $exception) {
            Log::error($exception->getMessage()." authid:$authId");
        }
    }
}
