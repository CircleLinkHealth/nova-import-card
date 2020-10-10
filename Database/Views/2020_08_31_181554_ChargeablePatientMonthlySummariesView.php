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

coalesce((
SELECT COUNT(*)
FROM calls
WHERE
inbound_cpm_id = cpms.patient_user_id

and (DATE(calls.called_date) between cpms.chargeable_month and LAST_DAY(cpms.chargeable_month))

and (
type IS NULL
OR type = 'call'
OR sub_type = 'Call Back'
)
and status in ('reached', 'not reached')
),0)

as no_of_calls,

coalesce((
SELECT COUNT(*) as no_of_calls FROM calls
WHERE
inbound_cpm_id = cpms.patient_user_id
and (DATE(calls.called_date) between cpms.chargeable_month and LAST_DAY(cpms.chargeable_month))
and (
type IS NULL
OR type = 'call'
OR sub_type = 'Call Back'
)
and status='reached'
),0) as no_of_successful_calls,

coalesce((SELECT SUM(duration) as total_time from lv_activities where patient_id=cpms.patient_user_id and chargeable_service_id=cpms.chargeable_service_id  AND (DATE(performed_at) between cpms.chargeable_month and LAST_DAY(cpms.chargeable_month))),0

)as total_time

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
