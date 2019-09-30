<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class AddAsapFieldToCallsViewsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        $startOfMonthQuery = 'mysql' === config('database.connections')[config('database.default')]['driver']
            ? "DATE_ADD(DATE_ADD(LAST_DAY(CONVERT_TZ(UTC_TIMESTAMP(),'UTC','America/New_York')), INTERVAL 1 DAY), INTERVAL - 1 MONTH)"
            : "date('now','start of month')"; //sqlite

        $viewName = 'calls_view';
        \DB::statement("DROP VIEW IF EXISTS ${viewName}");
        \DB::statement("
        CREATE VIEW ${viewName}
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
            u4.last_call,
            if (u5.ccm_time is null, 0, u5.ccm_time) as ccm_time,
            if (u5.bhi_time is null, 0, u5.bhi_time) as bhi_time,
            if (u5.no_of_calls is null, 0, u5.no_of_calls) as no_of_calls,
            if (u5.no_of_successful_calls is null, 0, u5.no_of_successful_calls) as no_of_successful_calls,
            u7.practice_id,
            u7.practice,
            u1.timezone,
            c.window_start as call_time_start, 
            c.window_end as call_time_end,
            c.asap,
            u6.preferred_call_days,
            if(pccm.id is null, false, true) as is_ccm,
            if(pbhi.id is null, false, true) as is_bhi,
            if(u3.scheduler is null, c.scheduler, u3.scheduler) as scheduler,
            u8.billing_provider,
            c.attempt_note,
            u4.general_comment,
            u4.ccm_status
        FROM 
            calls c
            left join (select u.id as patient_id, CONCAT(u.display_name) as patient, u.timezone from users u where u.deleted_at is null) as u1 on c.inbound_cpm_id = u1.patient_id

            left join (select u.id as nurse_id, CONCAT(u.first_name, ' ', u.last_name, ' ', u.suffix) as nurse from users u where u.deleted_at is null) as u2 on c.outbound_cpm_id = u2.nurse_id

            left join (select u.id as scheduler_id, u.display_name as `scheduler` from users u where u.deleted_at is null) as u3 on c.scheduler = u3.scheduler_id
            
            left join (select pi.user_id as patient_id, pi.last_contact_time as last_call, pi.no_call_attempts_since_last_success, pi.general_comment, pi.ccm_status from patient_info pi where pi.deleted_at is null and pi.ccm_status in ('enrolled', 'paused')) as u4 on c.inbound_cpm_id = u4.patient_id
            
            left join (select pms.patient_id, pms.ccm_time, pms.bhi_time, pms.no_of_successful_calls, pms.no_of_calls from patient_monthly_summaries pms where month_year = ${startOfMonthQuery}) u5 on c.inbound_cpm_id = u5.patient_id
            
			left join (select pi.user_id, GROUP_CONCAT(pcw.day_of_week) as preferred_call_days from patient_info pi left join patient_contact_window pcw on pi.id = pcw.patient_info_id where pi.deleted_at is null group by pi.user_id) as u6 on c.inbound_cpm_id = u6.user_id
			
			left join (select u.id as user_id, p.id as practice_id, p.display_name as practice from practices p join users u on u.program_id = p.id where p.active = 1) u7 on c.inbound_cpm_id = u7.user_id
			       
            left join patients_bhi_chargeable_view pbhi on c.inbound_cpm_id = pbhi.id
            
            left join patients_ccm_view pccm on c.inbound_cpm_id = pccm.id
            
            left join (select pbp.user_id as patient_id, u.display_name as billing_provider from users u join (select pctm.user_id, pctm.member_user_id from users u 		left join patient_care_team_members pctm on u.id = pctm.user_id where pctm.type = 'billing_provider') pbp on pbp.member_user_id = u.id) u8 on c.inbound_cpm_id = u8.patient_id

           
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
}
