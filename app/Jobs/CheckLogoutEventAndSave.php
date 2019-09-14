<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\LoginLogout;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
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

    /**
     * Execute the job.
     */
    public function handle()
    {
        $this->checkLogoutEvent();
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

    /**
     * @param $event
     * @param Carbon $date
     */
    public function saveLogoutEvent($event)
    {
        $endTime = $this->getEndTimeOfLatestActivityAfterLogin($event);

        try {
            $event->logout_time = $endTime;
            $event->was_edited = true;
            $event->save();
        } catch (\Exception $exception) {
            //@todo: maybe get next login time and set missing logout before that. If it is last login on table then set it to where??
            \Log::error(`Couldnt get end_time from page_timer table, for logout event $event->id`);
        }
    }

    public function getEndTimeOfLatestActivityAfterLogin($event)
    {
        $latestActivityAfterLogin = $event->activities->sortBy('end_time')->where('end_time', '>=', $event->login_time)
            ->where('end_time', '<=', Carbon::parse($event->login_time)->endOfDay())
            ->first();

        return Carbon::parse($latestActivityAfterLogin->end_time)->toDateTime();
    }

    /**
     * @return LoginLogout[]|Builder[]|\Illuminate\Database\Eloquent\Collection|Collection
     */
    public function getYesterdaysEvents()
    {
        return LoginLogout::with('activities')
            ->orderBy('created_at', 'asc')
            ->where([
                ['created_at', '>=', Carbon::parse($this->date)->startOfDay()],
                ['created_at', '<=', Carbon::parse($this->date)->endOfDay()],
            ])->get();
    }
}
