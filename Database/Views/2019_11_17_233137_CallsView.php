<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\SqlViews\BaseSqlView;

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 11/18/19
 * Time: 4:02 PM.
 */
class CallsView extends BaseSqlView
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
            c.is_manual,
            c.status,
            if(c.type = 'call' or c.type is null, 'call', c.sub_type) as type,
            u2.nurse_id,
            u2.nurse,
            u1.patient_id,
            u1.patient,
            c.scheduled_date,
            (select max(called_date) from calls where `status` in ('reached', 'not reached', 'ignored') and calls.inbound_cpm_id = c.inbound_cpm_id) as last_call,
            if (u5.ccm_time is null, 0, u5.ccm_time) as ccm_time,
            if (u5.bhi_time is null, 0, u5.bhi_time) as bhi_time,
            if (u5.no_of_calls is null, 0, u5.no_of_calls) as no_of_calls,
            if (u5.no_of_successful_calls is null, 0, u5.no_of_successful_calls) as no_of_successful_calls,
            u7.practice_id,
            u7.practice,
            u10.state,
            u1.timezone,
            c.window_start as call_time_start,
            c.window_end as call_time_end,
            c.asap,
            u4.preferred_call_days,
            if(pccm.id is null, false, true) as is_ccm,
            if(pbhi.id is null, false, true) as is_bhi,
            if(u3.scheduler is null, c.scheduler, u3.scheduler) as scheduler,
            u8.billing_provider,
            c.attempt_note,
            u4.general_comment,
            u4.ccm_status,
            u9.patient_nurse_id,
            u9.patient_nurse,
            u4.preferred_contact_language
            
        FROM
            calls c
            join (select u.id as patient_id, u.display_name as patient, u.timezone from users u where u.deleted_at is null) as u1 on c.inbound_cpm_id = u1.patient_id

            left join (select u.id as nurse_id, CONCAT(u.first_name, ' ', u.last_name, ' ', (if (u.suffix is null, '', u.suffix))) as nurse from users u) as u2 on c.outbound_cpm_id = u2.nurse_id

            left join (select u.id as scheduler_id, u.display_name as `scheduler` from users u) as u3 on c.scheduler = u3.scheduler_id

            left join (select pi.user_id as patient_id, pi.general_comment, pi.ccm_status, pi.preferred_contact_language, GROUP_CONCAT(pcw.day_of_week) as preferred_call_days
						from patient_info pi
						left join patient_contact_window pcw on pi.id = pcw.patient_info_id
						where pi.ccm_status in ('enrolled', 'paused')
						group by pi.user_id) as u4 on c.inbound_cpm_id = u4.patient_id
						
            left join (select pms.patient_id, pms.ccm_time, pms.bhi_time, pms.no_of_successful_calls, pms.no_of_calls from patient_monthly_summaries pms where month_year = DATE_ADD(DATE_ADD(LAST_DAY(CONVERT_TZ(UTC_TIMESTAMP(),'UTC','America/New_York')), INTERVAL 1 DAY), INTERVAL - 1 MONTH)) u5 on c.inbound_cpm_id = u5.patient_id

			left join (select u.id as user_id, p.id as practice_id, p.display_name as practice from practices p join users u on u.program_id = p.id where p.active = 1) u7 on c.inbound_cpm_id = u7.user_id

            left join patients_bhi_chargeable_view pbhi on c.inbound_cpm_id = pbhi.id

            left join patients_ccm_view pccm on c.inbound_cpm_id = pccm.id

            left join (select pbp.user_id as patient_id, u.display_name as billing_provider from users u join (select pctm.user_id, pctm.member_user_id from users u 		left join patient_care_team_members pctm on u.id = pctm.user_id where pctm.type = 'billing_provider') pbp on pbp.member_user_id = u.id) u8 on c.inbound_cpm_id = u8.patient_id

            left join (select pi.patient_user_id as patient_id, u.id as patient_nurse_id, CONCAT(u.first_name, ' ', u.last_name, ' ', (if (u.suffix is null, '', u.suffix))) as patient_nurse from users u join patients_nurses pi on u.id = pi.nurse_user_id) as u9 on c.inbound_cpm_id = u9.patient_id
            
            left join (select pi.user_id as patient_id, l.state from locations l left join patient_info pi on l.id = pi.preferred_contact_location) as u10 on c.inbound_cpm_id = u10.patient_id

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
        return 'calls_view';
    }
}
