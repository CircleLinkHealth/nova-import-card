<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\SqlViews\BaseSqlView;

class CallsViewNurses extends BaseSqlView
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
            c.id,
            if(c.type = 'call' or c.type is null, 'call', c.sub_type) as type,
            u2.nurse_id,
            u1.patient_id,
            u1.patient,
            c.scheduled_date,
            c.status,
            u4.last_call,
            if (u5.ccm_time is null, 0, u5.ccm_time) as ccm_time,
            if (u5.bhi_time is null, 0, u5.bhi_time) as bhi_time,
            if (u5.no_of_calls is null, 0, u5.no_of_calls) as no_of_calls,
            if (u5.no_of_successful_calls is null, 0, u5.no_of_successful_calls) as no_of_successful_calls,
            u7.practice,
            u1.timezone,
            c.window_start as call_time_start,
            c.window_end as call_time_end,
            c.asap,
            u6.preferred_call_days,
            u8.billing_provider,
            c.attempt_note,
            u4.general_comment,
            u4.ccm_status
        FROM
            calls c
            join (select u.id as patient_id, CONCAT(u.display_name) as patient, u.timezone from users u where u.deleted_at is null) as u1 on c.inbound_cpm_id = u1.patient_id

            left join (select u.id as nurse_id from users u) as u2 on c.outbound_cpm_id = u2.nurse_id

            left join (select pi.user_id as patient_id, pi.last_contact_time as last_call, pi.no_call_attempts_since_last_success, pi.general_comment, pi.ccm_status from patient_info pi where pi.ccm_status in ('enrolled', 'paused')) as u4 on c.inbound_cpm_id = u4.patient_id

            left join (select pms.patient_id, pms.ccm_time, pms.bhi_time, pms.no_of_successful_calls, pms.no_of_calls from patient_monthly_summaries pms where month_year = ${startOfMonthQuery}) u5 on c.inbound_cpm_id = u5.patient_id

			left join (select pi.user_id, GROUP_CONCAT(pcw.day_of_week) as preferred_call_days from patient_info pi left join patient_contact_window pcw on pi.id = pcw.patient_info_id group by pi.user_id) as u6 on c.inbound_cpm_id = u6.user_id

			left join (select u.id as user_id, p.id as practice_id, p.display_name as practice from practices p join users u on u.program_id = p.id where p.active = 1) u7 on c.inbound_cpm_id = u7.user_id
			
            left join (select pbp.user_id as patient_id, u.display_name as billing_provider from users u join (select pctm.user_id, pctm.member_user_id from users u
            
            left join patient_care_team_members pctm on u.id = pctm.user_id where pctm.type = 'billing_provider') pbp on pbp.member_user_id = u.id) u8 on c.inbound_cpm_id = u8.patient_id

        WHERE
            c.scheduled_date is not null
            AND (
                # calls need to be scheduled and in the future
                c.sub_type is null and c.status = 'scheduled' and c.scheduled_date >= DATE(CONVERT_TZ(UTC_TIMESTAMP(),'UTC','America/New_York'))
                OR
                # tasks can be in the past
                c.sub_type is not null
            )
      ");

        // we are using DATE(CONVERT_TZ(UTC_TIMESTAMP(),'UTC','America/New_York')) instead of CURDATE()
        // because we store scheduled_date in New York time (EST), but we the timezone in database can be anything (UTC or local)

        // removed where clause: c.status = 'scheduled' and c.scheduled_date >= DATE(CONVERT_TZ(UTC_TIMESTAMP(),'UTC','America/New_York'))
        // calls table is now an actions table.
        // we have tasks that may be due in the past
        // assuming that re-scheduler service is dropping past calls, we will only have type `task` that are in the past

        // update:
        // modified where clause to optimize query and cover comments above
    }

    /**
     * Get the name of the sql view.
     */
    public function getViewName(): string
    {
        return 'calls_view_nurses';
    }

    /**
     * Return a start of month query compatible with both sqlite and mysql.
     *
     * For this to work, you need to have time zone info in your database. Run the following command if you get nulls.
     * mysql_tzinfo_to_sql /usr/share/zoneinfo | mysql -u root mysql
     *
     * Alternative command: DATE_SUB(LAST_DAY(NOW()),INTERVAL DAY(LAST_DAY(NOW()))-1 DAY)
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
