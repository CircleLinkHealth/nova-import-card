<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class TimeTrackedPerDayView extends Migration
{
    const VIEW_NAME = 'time_tracked_per_day_view';

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        $viewName = self::VIEW_NAME;
        \DB::statement("DROP VIEW IF EXISTS ${viewName}");
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        $viewName = self::VIEW_NAME;

        \DB::statement("DROP VIEW IF EXISTS ${viewName}");
        $ran = \DB::statement("
        CREATE VIEW ${viewName}
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
                '0' as is_billable
                
            FROM `lv_page_timer`
            WHERE provider_id != 0
            GROUP BY user_id, `date`

            UNION ALL
          
            SELECT
                SUM(duration) as total_time,
                DATE_FORMAT(performed_at, '%Y-%m-%d') as `date`,
                provider_id as user_id,
                '1' as is_billable

            FROM `lv_activities`
            WHERE provider_id != 0
            AND logged_from = 'manual_input'
            GROUP BY user_id, `date`


          ) activities          
        GROUP BY user_id, `date`, is_billable;
        ");

        if ( ! $ran) {
            throw new \Exception('Could not create mysql view');
        }
    }
}
