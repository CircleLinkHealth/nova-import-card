<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Auth\Events\Login;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class PostLoginTasks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Login
     */
    protected $event;
    
    /**
     * Create a new job instance.
     *
     * @param Login $event
     */
    public function __construct(Login $event)
    {
        //
        $this->event = $event;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->event->user->last_login = Carbon::now()->toDateTimeString();
        $this->event->user->is_online  = true;
    
        $authyUser = optional($this->event->user->authyUser);
    
        if ($this->event->user->isAdmin() && (bool) config('auth.two_fa_enabled') && $authyUser->authy_id && ! $authyUser->is_authy_enabled) {
            $authyUser->is_authy_enabled = true;
            $authyUser->save();
        }
    
        $this->event->user->save();
    }
}
