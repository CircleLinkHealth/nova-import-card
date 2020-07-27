<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\LoginLogout;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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
     * @var int
     */
    private $id;

    /**
     * Create a new job instance.
     */
    public function __construct(Carbon $date, int $id)
    {
        $this->date = $date;
        $this->id   = $id;
    }

    /**
     * @return \DateTime|null
     */
    public function getEndTimeOfLatestActivityAfterLogin(LoginLogout $event)
    {
        //@todo: If it is the last activity(page_timer) for the day, then where to set the logout from?
        $latestActivityAfterLogin = $event->activities->sortBy('end_time')
            ->where('end_time', '>=', $event->login_time)
            ->where('end_time', '<=', Carbon::parse($event->login_time)->endOfDay())
            ->first();

        if (is_null($latestActivityAfterLogin)) {
            // We can exit loop or create a logout time at the end of day?
            //            return Carbon::parse($event->login_time)->copy()->endOfDay()->toDateTime();

            return null;
        }

        return $latestActivityAfterLogin->end_time;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        /** @var LoginLogout $loginEvent */
        $loginEvent = $this->eventWithActivities();

        if (is_null($loginEvent)) {
            return;
        }

        /** @var Carbon $latestActivityEndTime */
        $latestActivityEndTime = $this->getEndTimeOfLatestActivityAfterLogin($loginEvent);

        // If is null then exit loop.
        if (is_null($latestActivityEndTime)) {
            return;
        }

        if (empty($loginEvent->logout_time)) {
            try {
                $loginEvent->logout_time     = $latestActivityEndTime;
                $loginEvent->was_edited      = true;
                $loginEvent->duration_in_sec = $latestActivityEndTime->diffInSeconds($loginEvent->login_time);
                $loginEvent->save();
            } catch (\Exception $exception) {
                \Log::error("Could not get end_time from page_timer table, for logout event $loginEvent->id");
            }
        }
    }

    private function eventWithActivities()
    {
        return LoginLogout::with([
            'activities' => function ($activity) {
                $activity->whereBetween('end_time', [$this->date->copy()->startOfDay(), $this->date->copy()->endOfDay()]);
            },
        ])->where('id', $this->id)
            ->whereHas('activities', function ($activity) {
                $activity->whereBetween('end_time', [$this->date->copy()->startOfDay(), $this->date->copy()->endOfDay()]);
            })->first();
    }
}
