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

        $startOfMonthQuery = env('DB_CONNECTION', 'mysql') === "mysql" ?
            "DATE_ADD(DATE_ADD(LAST_DAY(NOW()), INTERVAL 1 DAY), INTERVAL - 1 MONTH)" :
            "date('now','start of month')"; //sqlite

        $viewName = "calls_view";
        \DB::statement("DROP VIEW IF EXISTS $viewName");
        \DB::statement("
        CREATE VIEW $viewName
        AS
        SELECT
            c.id,
            c.is_manual,
            u2.nurse_id,
            u2.nurse,
            u1.patient_id,
            u1.patient,
            c.scheduled_date, 
            u4.last_call,
            if (u5.ccm_time is null, 0, u5.ccm_time) as ccm_time,
            if (u5.bhi_time is null, 0, u5.bhi_time) as bhi_time,
            if (u5.no_of_successful_calls is null, 0, u5.no_of_successful_calls) as no_of_successful_calls,
            u7.practice_id,
            u7.practice,
            c.window_start as call_time_start, 
            c.window_end as call_time_end,
            u6.preferred_call_days,
            if(pccm.id is null, false, true) as is_ccm,
            if(pbhi.id is null, false, true) as is_bhi,
            if(u3.scheduler is null, c.scheduler, u3.scheduler) as scheduler
        FROM 
            calls c
            left join (select u.id as patient_id, CONCAT(u.first_name, ' ', u.last_name) as patient from users u where u.deleted_at is null) as u1 on c.inbound_cpm_id = u1.patient_id

            left join (select u.id as nurse_id, CONCAT(u.first_name, ' ', u.last_name, ' ', u.suffix) as nurse from users u where u.deleted_at is null) as u2 on c.outbound_cpm_id = u2.nurse_id

            left join (select u.id as scheduler_id, CONCAT(u.first_name, ' ', u.last_name, ' ', u.suffix) as `scheduler` from users u where u.deleted_at is null) as u3 on c.scheduler = u3.scheduler_id
            
            left join (select pi.user_id as patient_id, pi.last_contact_time as last_call, pi.no_call_attempts_since_last_success from patient_info pi where pi.deleted_at is null and pi.ccm_status = 'enrolled') as u4 on c.inbound_cpm_id = u4.patient_id
            
            left join (select pms.patient_id, pms.ccm_time, pms.bhi_time, pms.no_of_successful_calls from patient_monthly_summaries pms where month_year = $startOfMonthQuery) u5 on c.inbound_cpm_id = u5.patient_id
            
			left join (select pi.user_id, GROUP_CONCAT(pcw.day_of_week) as preferred_call_days from patient_info pi left join patient_contact_window pcw on pi.id = pcw.patient_info_id where pi.deleted_at is null group by pi.user_id) as u6 on c.inbound_cpm_id = u6.user_id
			
			left join (select u.id as user_id, p.id as practice_id, p.display_name as practice from practices p join users u on u.program_id = p.id where p.active = 1) u7 on c.inbound_cpm_id = u7.user_id
			       
            left join patients_bhi_chargeable_view pbhi on c.inbound_cpm_id = pbhi.id
            
            left join patients_ccm_view pccm on c.inbound_cpm_id = pccm.id
           
        WHERE
            c.status = 'scheduled' and c.scheduled_date >= CURDATE()
      ");
    }
}
