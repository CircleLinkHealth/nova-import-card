<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Auth\Events\Login;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PostLoginTasks implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    /**
     * @var Login
     */
    protected $event;

    /**
     * Create a new job instance.
     */
    public function __construct(Login $event)
    {
        $this->event = $event;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $this->event->user->last_login = Carbon::now()->toDateTimeString();
        $this->event->user->is_online  = true;

        $authyUser = optional($this->event->user->authyUser);

        if (isAllowedToSee2FA($this->event->user) && (bool) config('auth.two_fa_enabled') && $authyUser->authy_id && ! $authyUser->is_authy_enabled) {
            $authyUser->is_authy_enabled = true;
            $authyUser->save();
        }

        $this->event->user->save();
    }
}
