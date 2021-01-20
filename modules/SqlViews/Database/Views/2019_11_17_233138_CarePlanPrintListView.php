<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\SqlViews\BaseSqlView;

class CarePlanPrintListView extends BaseSqlView
{
    /**
     * Create the sql view.
     */
    public function createSqlView(): bool
    {
        $startOfMonthQuery = $this->safeStartOfMonthQuery();

        return \DB::statement("
        CREATE VIEW {$this->getViewName()}
        AS
SELECT
c.id as care_plan_id,
c.status as care_plan_status,
c.last_printed,
c.provider_date,
u1.patient_id,
u1.patient_full_name,
u1.patient_first_name,
u1.patient_last_name,
u1.patient_registered,
pi.patient_info_id,
pi.patient_dob,
pi.patient_ccm_status,
pra.primary_practice_id,
pra.practice_name,
u2.approver_full_name,
ct.provider_full_name,
pms.patient_ccm_time

FROM
care_plans c

LEFT JOIN (select u.id as patient_id, u.program_id, CONCAT_WS(' ', u.first_name, u.last_name) as patient_full_name, u.first_name as patient_first_name, u.last_name as patient_last_name, u.user_registered as patient_registered from users u where u.deleted_at is null) as u1 on c.user_id = u1.patient_id

LEFT JOIN (select p.user_id, p.id as patient_info_id, p.ccm_status as patient_ccm_status, p.birth_date as patient_dob from patient_info p ) as pi ON pi.user_id = u1.patient_id

LEFT JOIN (select p.id as primary_practice_id, p.display_name as practice_name from practices p) as pra ON pra.primary_practice_id=u1.program_id

LEFT JOIN (select u.id, CONCAT_WS(' ', u.first_name, u.last_name, u.suffix) as approver_full_name from users u where u.deleted_at is null) as u2 on c.provider_approver_id=u2.id

LEFT JOIN (select ct.user_id, ct.member_user_id, ct.type, CONCAT_WS(' ', u.first_name, u.last_name, u.suffix) as provider_full_name from patient_care_team_members ct left join users u on ct.member_user_id = u.id where ct.type = 'billing_provider') as ct on ct.user_id=c.user_id

LEFT JOIN (select pms.id, pms.patient_id, pms.month_year, pms.ccm_time as patient_ccm_time from patient_monthly_summaries pms where pms.month_year={$startOfMonthQuery}) as pms on pms.patient_id = u1.patient_id

WHERE pi.patient_ccm_status = 'enrolled'
      ");
    }

    /**
     * Get the name of the sql view.
     */
    public function getViewName(): string
    {
        return 'careplan_print_list_view';
    }

    /**
     * Return a start of month query compatible with both sqlite and mysql.
     *
     * @return string
     */
    private function safeStartOfMonthQuery()
    {
        return 'mysql' === config('database.connections')[config('database.default')]['driver']
            ? "DATE_ADD(DATE_ADD(LAST_DAY(CONVERT_TZ(UTC_TIMESTAMP(),'UTC','America/New_York')), INTERVAL 1 DAY), INTERVAL - 1 MONTH)"
            : "date('now','start of month')"; //sqlite
    }
}
