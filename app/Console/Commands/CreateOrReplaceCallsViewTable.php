<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateOrReplaceCallsViewTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'view:CreateOrReplaceCallsViewTable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create or Replace Calls View Table (calls_view)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        \DB::statement("
        CREATE OR REPLACE VIEW calls_view
        AS
        SELECT
            c.id,
            c.is_manual,
            c.note_id,
            c.attempt_note,
            u2.nurse_id,
            u2.nurse,
            u1.patient_id, 
            u1.patient, 
            c.scheduled_date, 
            u4.no_call_attempts_since_last_success, 
            u4.last_call, 
            u5.ccm_time, 
            u5.bhi_time,
            u5.no_of_successful_calls,
            u7.practice_id, 
            u7.practice, 
            u6.call_time_start, 
            u6.call_time_end,
            u1.patient_created_at,
            u6.preferred_call_days,
            u6.patient_status,
            u8.provider,
            if(u3.scheduler is not null, u3.scheduler, c.scheduler) as `scheduler`,
            u9.is_bhi,
            u10.is_ccm
        FROM 
            calls c,
            
            (select u.id as patient_id, CONCAT(u.first_name, ' ', u.last_name) as patient, u.created_at as patient_created_at, c.id as call_id from users u join calls c on u.id = c.inbound_cpm_id where c.status = 'scheduled' and c.scheduled_date >= CURDATE()) as u1,
            
            (select u.id as nurse_id, u.display_name as nurse, c.id as call_id from users u join calls c on u.id = c.outbound_cpm_id where c.status = 'scheduled' and c.scheduled_date >= CURDATE()) as u2,
            
            (select u.display_name as `scheduler`, c.id as call_id from users u right join calls c on u.id = c.scheduler where c.status = 'scheduled' and c.scheduled_date >= CURDATE()) as u3,
            
            (select c.id as call_id, pi.last_contact_time as last_call, pi.no_call_attempts_since_last_success from patient_info pi join calls c on c.inbound_cpm_id = pi.user_id where pi.ccm_status = 'enrolled' and c.status = 'scheduled' and c.scheduled_date >= CURDATE()) as u4,
            
            (select c.id as call_id, if(pms.ccm_time is null, 0, pms.ccm_time) as ccm_time, if(pms.bhi_time is null, 0, pms.bhi_time) as bhi_time, if(pms.no_of_successful_calls is null, 0, pms.no_of_successful_calls) as no_of_successful_calls from calls c left join (select * from patient_monthly_summaries where month_year = DATE_ADD(DATE_ADD(LAST_DAY(NOW()), INTERVAL 1 DAY), INTERVAL - 1 MONTH)) pms on pms.patient_id = c.inbound_cpm_id where c.status = 'scheduled' and c.scheduled_date >= CURDATE()) as u5,
            
            (select c.id as call_id, pi.user_id, pi.ccm_status as patient_status, pcw.patient_info_id, MIN(pcw.window_time_start) as call_time_start, MAX(pcw.window_time_end) as call_time_end, GROUP_CONCAT(pcw.day_of_week SEPARATOR ',') as preferred_call_days
            from patient_contact_window pcw
            join patient_info pi on pcw.patient_info_id = pi.id
            join calls c on pi.user_id = c.inbound_cpm_id
            where c.status = 'scheduled' and c.scheduled_date >= CURDATE()
            group by pcw.patient_info_id, pi.user_id, c.id) as u6,
            
            (select c.id as call_id, p.id as practice_id, p.display_name as practice
            from users u
            join practices p on u.program_id = p.id 
            join calls c on u.id = c.inbound_cpm_id
            where p.active = 1 and c.status = 'scheduled' and c.scheduled_date >= CURDATE()) as u7,
            
            (select c.id as call_id, u.display_name as provider
            from calls c
            left join (select * from patient_care_team_members where type = 'billing_provider') pctm on c.inbound_cpm_id = pctm.user_id
            left join users u on u.id = pctm.member_user_id
            where c.status = 'scheduled' and c.scheduled_date >= CURDATE()) as u8,
            
            (select c.id as call_id, if(pbhi.id is null, false, true) as is_bhi
            from calls c
            left join patients_bhi_chargeable_view pbhi on c.inbound_cpm_id = pbhi.id
            where c.status = 'scheduled' and c.scheduled_date >= CURDATE()) as u9,
            
            (select c.id as call_id, if(pccm.id is null, false, true) as is_ccm
            from calls c
            left join patients_ccm_view pccm on c.inbound_cpm_id = pccm.id
            where c.status = 'scheduled' and c.scheduled_date >= CURDATE()) as u10
             
        WHERE
            c.status = 'scheduled' and c.scheduled_date >= CURDATE() and u1.call_id = c.id and u2.call_id = c.id and u3.call_id = c.id and u4.call_id = c.id and u5.call_id = c.id and u6.call_id = c.id and u7.call_id = c.id and u8.call_id = c.id and u9.call_id = c.id and u10.call_id = c.id
        ");
    }
}
