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
            $scheduleIconClass = '';
            $iClassStyle = '';

            if ($userIsCareCoach) {
                $hasCurrentWeekWindows = \Cache::remember("current_week_windows_for_nurse_user_id_[$user->id]", 2, function () use ($user) {
                    return  $user->nurseInfo->currentWeekWindows()->exists();
                });

                $scheduleIconClass = $hasCurrentWeekWindows
                    ? 'top-nav-item-icon glyphicon glyphicon-calendar'
                    : 'fa fa-exclamation';

                $iClassStyle = 'fa fa-exclamation' === $scheduleIconClass
                    ? 'color: background: rgb(255, 255, 255);
                    font-size: 12px;
                    background: rgb(238, 66, 20);
                    border-radius: 0.8em;
                    display: inline-block;
                    font-weight: bold;
                    line-height: 1.6em;
                    margin-right: 5px;
                    text-align: center;
                    width: 1.6em;
                    animation: shake-animation 3.72s ease infinite;
                    transform-origin: 50% 50%;;'
                    : '';
            }

            $view->with(compact([
                'user',
                'userIsCareCoach',
                'scheduleIconClass',
                'iClassStyle',
            ]));
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
