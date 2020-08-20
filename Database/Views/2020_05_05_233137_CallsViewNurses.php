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
        return \DB::statement("
        CREATE VIEW {$this->getViewName()}
        AS
        SELECT
            c.id,
            if(c.type = 'call' or c.type is null, 'call', c.sub_type) as type,
            c.outbound_cpm_id as nurse_id,
            u1.patient_id,
            u1.patient,
            c.scheduled_date,
            c.status,
            (select max(called_date) from calls where `status` in ('reached', 'not reached', 'ignored') and calls.inbound_cpm_id = c.inbound_cpm_id) as last_call,
            if (u5.ccm_time is null, 0, u5.ccm_time) as ccm_time,
            if (u5.bhi_time is null, 0, u5.bhi_time) as bhi_time,
            if (u5.no_of_calls is null, 0, u5.no_of_calls) as no_of_calls,
            if (u5.no_of_successful_calls is null, 0, u5.no_of_successful_calls) as no_of_successful_calls,
            u7.practice,
            u1.timezone,
            c.window_start as call_time_start,
            c.window_end as call_time_end,
            c.asap,
            u2.preferred_call_days,
            u8.billing_provider,
            c.attempt_note,
            u2.general_comment,
            u2.ccm_status
        FROM
            calls c
            join (select u.id as patient_id, u.display_name as patient, u.timezone from users u where u.deleted_at is null) as u1 on c.inbound_cpm_id = u1.patient_id
            
            left join (select pi.user_id as patient_id, pi.general_comment, pi.ccm_status, GROUP_CONCAT(pcw.day_of_week) as preferred_call_days
						from patient_info pi
						left join patient_contact_window pcw on pi.id = pcw.patient_info_id
						where pi.ccm_status in ('enrolled', 'paused')
						group by pi.user_id) as u2 on c.inbound_cpm_id = u2.patient_id

            left join (select pms.patient_id, pms.ccm_time, pms.bhi_time, pms.no_of_successful_calls, pms.no_of_calls from patient_monthly_summaries pms where month_year = DATE_ADD(DATE_ADD(LAST_DAY(CONVERT_TZ(UTC_TIMESTAMP(),'UTC','America/New_York')), INTERVAL 1 DAY), INTERVAL - 1 MONTH)) u5 on c.inbound_cpm_id = u5.patient_id

			left join (select u.id as user_id, p.id as practice_id, p.display_name as practice from practices p join users u on u.program_id = p.id where p.active = 1) u7 on c.inbound_cpm_id = u7.user_id
			
            left join (select pbp.user_id as patient_id, u.display_name as billing_provider from users u join (select pctm.user_id, pctm.member_user_id from users u
                                                                                                                left join patient_care_team_members pctm on u.id = pctm.user_id where pctm.type = 'billing_provider') pbp on pbp.member_user_id = u.id limit 1) u8 on c.inbound_cpm_id = u8.patient_id
        WHERE
            # calls need to be scheduled and in the future
            (c.type = 'call' and c.status = 'scheduled' and c.scheduled_date >= DATE(CONVERT_TZ(UTC_TIMESTAMP(),'UTC','America/New_York')))
            OR
            # tasks can be in the past
            c.type != 'call'
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
}
