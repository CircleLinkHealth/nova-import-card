<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\LoginLogout;
use Carbon\Carbon;
use Illuminate\Auth\Events\Login;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LogSuccessfulLoginToDB implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    /**
     * @var Login
     */
    private $event;

    /**
     * Create a new job instance.
     *
     * @param Login $event
     */
    public function __construct(Login $event)
    {
        $this->event = $event;
    }

    /**
     * Execute the job.
     *
     * @param Request $request
     */
    public function handle(Request $request)
    {
        try {
            LoginLogout::create([
                'user_id'    => $this->event->user->id,
                'login_time' => now(),
                'ip_address' => getIpAddress(),
            ]);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }
    }
}
