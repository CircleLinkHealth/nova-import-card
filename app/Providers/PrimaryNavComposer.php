<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use App\FullCalendar\NurseCalendarService;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class PrimaryNavComposer extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer(['partials.providerUI.primarynav'], function ($view) {
            $user = auth()->user();
            $userIsCareCoach = $user->isCareCoach();
            $hasCurrentWeekWindows = false;
            $reportData = [];

            if ($userIsCareCoach) {
                $hasCurrentWeekWindows = $this->getCurrentWeekWindows($user);

                $date = Carbon::now();
                if ( ! \Cache::has("daily-report-for-{$user->id}-{$date->toDateString()}")) {
                    $reportData = (new NurseCalendarService())->nurseDailyReportForDate($user->id, $date);
                }
            }

            $view->with(compact(
                'user',
                'userIsCareCoach',
                'hasCurrentWeekWindows',
                'reportData'
            ));
        });
    }

    /**
     * @param $user
     *
     * @return mixed
     */
    public function getCurrentWeekWindows($user)
    {
        return \Cache::remember("current_week_windows_for_nurse_user_id_[$user->id]", 2, function () use ($user) {
            return $user->nurseInfo->currentWeekWindows()->exists();
        });
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
    }
}
