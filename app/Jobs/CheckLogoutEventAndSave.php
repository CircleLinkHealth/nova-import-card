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

class CheckLogoutEventAndSave implements ShouldQueue
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

    public function checkLogoutEvent()
    {
        $yesterdaysEvents = $this->getYesterdaysEvents();
        foreach ($yesterdaysEvents as $event) {
            if (null === $event->logout_time) {
                $this->saveLogoutEvent($event);
            }
        }
    }

    public function getEndTimeOfLatestActivityAfterLogin($event)
    {
        //@todo: If it is the last activity(page_timer) for the day, then where to set the logout from?
        $latestActivityAfterLogin = $event->activities->sortBy('end_time')->where('end_time', '>=', $event->login_time)
            ->where('end_time', '<=', Carbon::parse($event->login_time)->endOfDay())
            ->first();

        return Carbon::parse($latestActivityAfterLogin->end_time)->toDateTime();
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
        $this->checkLogoutEvent();
    }

    /**
     * @param $event
     * @param Carbon $date
     */
    public function saveLogoutEvent($event)
    {
        $endTime = $this->getEndTimeOfLatestActivityAfterLogin($event);
        try {
            $event->logout_time = $endTime;
            $event->was_edited  = true;
            $event->save();
        } catch (\Exception $exception) {
            \Log::error(`Couldnt get end_time from page_timer table, for logout event $event->id`);
        }
    }
}
