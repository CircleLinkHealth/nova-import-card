<?php

namespace App\Jobs;

use App\LoginLogout;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CalculateAndSaveLoginLogoutActivity implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Carbon
     */
    private $date;

    /**
     * Create a new job instance.
     *
     * @param Carbon $date
     */
    public function __construct(Carbon $date)
    {
        //
        $this->date = $date;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->calculateLoginLogoutActivity();
    }

    public function calculateLoginLogoutActivity()
    {
        $yesterdaysEvents = LoginLogout::where([
            ['created_at', '>=', Carbon::parse($this->date)->startOfDay()],
            ['created_at', '<=', Carbon::parse($this->date)->endOfDay()],
        ])->get();

        foreach ($yesterdaysEvents as $event) {
            $loginTime  = Carbon::parse($event->login_time);
            $logoutTime = Carbon::parse($event->logout_time);

            $event->duration_in_sec = $logoutTime->diffInSeconds($loginTime);
            $event->save();
        }
    }
}
