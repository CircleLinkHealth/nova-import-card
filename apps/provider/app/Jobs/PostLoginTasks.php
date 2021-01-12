<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
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
     * @var User
     */
    private $user;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $this->user->last_login = Carbon::now()->toDateTimeString();
        $this->user->is_online  = true;

        $authyUser = optional($this->user->authyUser);

        if ( ! $authyUser->is_authy_enabled && $authyUser->authy_id && isAllowedToSee2FA($this->user) && (bool) config('auth.two_fa_enabled')) {
            $authyUser->is_authy_enabled = true;
            $authyUser->save();
        }

        $this->user->save();
    }
}
