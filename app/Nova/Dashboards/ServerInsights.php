<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Dashboards;

use Laravel\Nova\Dashboard;

class ServerInsights extends Dashboard
{
    /**
     * Get the cards for the dashboard.
     *
     * @return array
     */
    public function cards()
    {
        return [
            new \Vink\NovaCacheCard\CacheCard(),
            new \Kreitje\NovaHorizonStats\JobsPastHour(),
            new \Kreitje\NovaHorizonStats\FailedJobsPastHour(),
            new \Kreitje\NovaHorizonStats\Processes(),
            new \Kreitje\NovaHorizonStats\Workload(),
        ];
    }

    public static function label()
    {
        return 'Server Insights';
    }

    /**
     * Get the URI key for the dashboard.
     *
     * @return string
     */
    public static function uriKey()
    {
        return 'server-insights';
    }
}
