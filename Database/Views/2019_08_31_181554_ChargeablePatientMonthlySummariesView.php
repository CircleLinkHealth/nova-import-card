<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\SqlViews\BaseSqlView;

class ChargeablePatientMonthlySummariesView extends BaseSqlView
{
    /**
     * Create the sql view.
     */
    public function createSqlView(): bool
    {
        return \DB::statement("
        CREATE VIEW {$this->getViewName()}
        AS
        SELECT cpms.*,
            cs.code as chargeable_service_code,
            cs.display_name as chargeable_service_name,

        coalesce((
            SELECT SUM(duration) as total_time
            FROM lv_activities
            WHERE patient_id = cpms.patient_user_id
            and chargeable_service_id = cpms.chargeable_service_id
            AND (DATE(performed_at) between cpms.chargeable_month and LAST_DAY(cpms.chargeable_month))
        ),0) as total_time

        from chargeable_patient_monthly_summaries cpms
        left join chargeable_services cs on cs.id=cpms.chargeable_service_id
    ");
    }

    /**
     * Get the name of the sql view.
     */
    public function getViewName(): string
    {
        return 'chargeable_patient_monthly_summaries_view';
    }
}
