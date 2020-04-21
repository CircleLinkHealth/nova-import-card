<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Services\LoginLogoutActivityService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class CalculateAndSaveLoginLogoutActivity implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    /**
     * @var Carbon
     */
    private $date;

    /**
     * Create a new job instance.
     */
    public function __construct(Carbon $date)
    {
        $this->date = $date;
    }

    public function calculateLoginLogoutActivity()
    {
        $yesterdaysEvents = $this->getYesterdaysEvents();
        foreach ($yesterdaysEvents as $event) {
            $loginTime  = $event->login_time;
            $logoutTime = $event->logout_time;

            $event->duration_in_sec = $logoutTime->diffInSeconds($loginTime);
            $event->save();
        }
    }

    /**
     * @return Collection
     */
    public function getYesterdaysEvents()
    {
        return LoginLogoutActivityService::yesterdaysActivity($this->date);
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $this->calculateLoginLogoutActivity();
    }
}
