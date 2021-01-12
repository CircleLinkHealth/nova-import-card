<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\SqlViews\BaseSqlView;

class TimeTrackedPerDayView extends BaseSqlView
{
    /**
     * Create the sql view.
     */
    public function createSqlView(): bool
    {
        return \DB::statement("
        CREATE VIEW {$this->getViewName()}
        AS
        
        SELECT
            SUM(activities.total_time) as total_time,
            `date`,
            user_id,
            is_billable

        FROM (
    
            SELECT
                SUM(duration) as total_time,
                DATE_FORMAT(start_time, '%Y-%m-%d') as `date`,
                provider_id as user_id,
                FALSE as is_billable
                
            FROM `lv_page_timer`
            WHERE provider_id != 0
            GROUP BY user_id, `date`

            UNION ALL
          
            SELECT
                SUM(duration) as total_time,
                DATE_FORMAT(performed_at, '%Y-%m-%d') as `date`,
                provider_id as user_id,
                TRUE as is_billable

            FROM `lv_activities`
            WHERE provider_id != 0
            AND logged_from = 'manual_input'
            GROUP BY user_id, `date`


          ) activities
        GROUP BY user_id, `date`, is_billable;
      ");
    }

    /**
     * Get the name of the sql view.
     */
    public function getViewName(): string
    {
        return 'time_tracked_per_day_view';
    }
}
