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
        $startOfMonthQuery = "cast(date_trunc('month', current_date) as date)";

        return \DB::statement("
        CREATE VIEW {$this->getViewName()}
        AS
        SELECT
            c.id,
            c.is_manual,
            c.status,
            CASE WHEN c.type = 'call' or c.type is null
                THEN 'call'
                ELSE c.sub_type
            END
            as type,
            u2.nurse_id,
            u2.nurse,
            u1.patient_id,
            u1.patient,
            c.scheduled_date,
            u4.last_call,
            CASE WHEN u5.ccm_time is null
                THEN 0
                ELSE u5.ccm_time
            END
            as ccm_time,
            CASE WHEN u5.bhi_time is null
                THEN 0
                ELSE u5.bhi_time
            END
            as bhi_time,
            CASE WHEN u5.no_of_calls is null
                THEN 0
                ELSE u5.no_of_calls
            END
            as no_of_calls,
            CASE WHEN u5.no_of_successful_calls is null
                THEN 0
                ELSE u5.no_of_successful_calls
            END
            as no_of_successful_calls,
            u7.practice_id,
            u7.practice,
            u1.timezone,
            c.window_start as call_time_start,
            c.window_end as call_time_end,
            c.asap,
            u6.preferred_call_days,
            CASE WHEN pccm.id is null
                THEN false
                ELSE true
            END
            as is_ccm,
            CASE WHEN pbhi.id is null
                THEN false
                ELSE true
            END
            as is_bhi,
            CASE WHEN u3.scheduler is null
                THEN c.scheduler
                ELSE u3.scheduler
            END as scheduler,
            u8.billing_provider,
            c.attempt_note,
            u4.general_comment,
            u4.ccm_status,
            u9.patient_nurse_id,
            u9.patient_nurse
        FROM
            calls c
            left join (select u.id as patient_id, CONCAT(u.display_name) as patient, u.timezone from users u where u.deleted_at is null) as u1 on c.inbound_cpm_id = u1.patient_id


            left join (select u.id as nurse_id, CONCAT(u.first_name, ' ', u.last_name, ' ', (CASE WHEN u.suffix is null THEN '' ELSE u.suffix END)) as nurse from users u where u.deleted_at is null) as u2 on c.outbound_cpm_id = u2.nurse_id

            left join (select u.id as scheduler_id, u.display_name as scheduler from users u where u.deleted_at is null) as u3 on c.scheduler::INT = u3.scheduler_id
            
            left join (select pi.user_id as patient_id, pi.last_contact_time as last_call, pi.no_call_attempts_since_last_success, pi.general_comment, pi.ccm_status from patient_info pi where pi.deleted_at is null and pi.ccm_status in ('enrolled', 'paused')) as u4 on c.inbound_cpm_id = u4.patient_id
            
            left join (select pms.patient_id, pms.ccm_time, pms.bhi_time, pms.no_of_successful_calls, pms.no_of_calls from patient_monthly_summaries pms where month_year = ${startOfMonthQuery}) u5 on c.inbound_cpm_id = u5.patient_id
            
			left join (select pi.user_id, string_agg(pcw.day_of_week::TEXT, ',') as preferred_call_days from patient_info pi left join patient_contact_window pcw on pi.id = pcw.patient_info_id where pi.deleted_at is null group by pi.user_id) as u6 on c.inbound_cpm_id = u6.user_id
			
			left join (select u.id as user_id, p.id as practice_id, p.display_name as practice from practices p join users u on u.program_id = p.id where p.active = true) u7 on c.inbound_cpm_id = u7.user_id
			       
            left join patients_bhi_chargeable_view pbhi on c.inbound_cpm_id = pbhi.id
            
            left join patients_ccm_view pccm on c.inbound_cpm_id = pccm.id
            
            left join (select pbp.user_id as patient_id, u.display_name as billing_provider from users u join (select pctm.user_id, pctm.member_user_id from users u 		left join patient_care_team_members pctm on u.id = pctm.user_id where pctm.type = 'billing_provider') pbp on pbp.member_user_id = u.id) u8 on c.inbound_cpm_id = u8.patient_id

            left join (select pi.patient_user_id as patient_id, u.id as patient_nurse_id, CONCAT(u.first_name, ' ', u.last_name, ' ', (CASE WHEN u.suffix is null THEN '' ELSE u.suffix END)) as patient_nurse from users u join patients_nurses pi on u.id = pi.nurse_user_id where u.deleted_at is null) as u9 on c.inbound_cpm_id = u9.patient_id
         
        WHERE
            c.scheduled_date is not null
      ");

        // we are using DATE(CONVERT_TZ(UTC_TIMESTAMP(),'UTC','America/New_York')) instead of CURDATE()
        // because we store scheduled_date in New York time (EST), but we the timezone in database can be anything (UTC or local)

        // removed where clause: c.status = 'scheduled' and c.scheduled_date >= DATE(CONVERT_TZ(UTC_TIMESTAMP(),'UTC','America/New_York'))
        // calls table is now an actions table.
        // we have tasks that may be due in the past
        // assuming that re-scheduler service is dropping past calls, we will only have type `task` that are in the past
    }

    /**
     * Get the name of the sql view.
     */
    public function getViewName(): string
    {
        return 'calls_view';
    }
}
