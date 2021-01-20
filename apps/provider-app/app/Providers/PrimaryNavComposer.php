<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Services\NurseCalendarService;
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
            $reportData = [];
            $hasNotCurrentWeekWindows = false;
            $enableDailyReportPopUp = false;

            if ($userIsCareCoach) {
                $date = Carbon::yesterday();
                $hasNotCurrentWeekWindows = \Cache::remember("current_week_windows_for_nurse_user_id_[$user->id]", 2, function () use ($user) {
                    return $user->nurseInfo->currentWeekWindows()->exists();
                });

                $cacheKey = "daily-report-for-{$user->id}-{$date->toDateString()}";
                $enableDailyReportPopUp = enableDailyReportToPopUp($user->id);

                if ($enableDailyReportPopUp && ! \Cache::has($cacheKey)) {
                    $reportData = (new NurseCalendarService())->nurseDailyReportForDate($user->id, $date, $cacheKey)->first();
                }
            }

            $view->with(compact(
                'user',
                'userIsCareCoach',
                'reportData',
                'hasNotCurrentWeekWindows',
                'enableDailyReportPopUp'
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