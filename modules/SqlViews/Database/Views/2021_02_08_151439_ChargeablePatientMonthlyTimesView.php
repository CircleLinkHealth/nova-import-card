<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\SqlViews\BaseSqlView;

class ChargeablePatientMonthlyTimesView extends BaseSqlView
{
    /**
     * Create the sql view.
     */
    public function createSqlView(): bool
    {
        return DB::statement("
        CREATE VIEW {$this->getViewName()} AS
        select patient_id as patient_user_id, chargeable_service_id, DATE(DATE_FORMAT(performed_at, '%Y-%m-01')) as chargeable_month, sum(duration) as total_time
        from lv_activities
        group by patient_user_id, chargeable_service_id, chargeable_month;
       ");
    }

    /**
     * Get the name of the sql view.
     */
    public function getViewName(): string
    {
        return 'chargeable_patient_monthly_times_view';
    }

    public function shouldRun(): bool
    {
        return isCpm();
    }
}
