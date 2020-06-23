<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

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

            if ($userIsCareCoach) {
                $hasCurrentWeekWindows = \Cache::remember("current_week_windows_for_nurse_user_id_[$user->id]", 2, function () use ($user) {
                    return $user->nurseInfo->currentWeekWindows()->exists();
                });
            }

            $view->with(compact(
                'user',
                'userIsCareCoach',
                'hasCurrentWeekWindows',
            ));
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
