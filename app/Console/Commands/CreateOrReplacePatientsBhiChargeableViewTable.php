<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateOrReplacePatientsBhiChargeableViewTable extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create or Replace Chargeable BHI Patients View Table (patients_bhi_chargeable_view)';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'view:CreateOrReplacePatientsBhiChargeableViewTable';

    /**
     * Create a new command instance.
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
        $viewName = 'patients_bhi_chargeable_view';
        \DB::statement("DROP VIEW IF EXISTS ${viewName}");
        \DB::statement("
        CREATE VIEW ${viewName}
        AS
        SELECT 
            u.id
        FROM users u
        JOIN patient_info p on u.id = p.user_id
        WHERE
            u.deleted_at is null
            and
	        p.ccm_status = 'enrolled'
	        and
	        (p.consent_date >= '2018-07-23' or exists (select * from notes n where n.patient_id = u.id and n.type = 'Consented to BHI'))
	        and
	        exists (
	            select *
	        	from ccd_problems ccd
		        join cpm_problems cpm on ccd.cpm_problem_id = cpm.id
        		where ccd.deleted_at is null and ccd.is_monitored = 1 and cpm.is_behavioral = 1 and patient_id = u.id
	        )
	        and
	        exists (
		        select *
		        from practices p
		        join chargeables c on p.id = c.chargeable_id
		        join chargeable_services cs on cs.id = c.chargeable_service_id 
		        where c.chargeable_type = 'App\\\\Practice' and code = 'CPT 99484' and u.program_id = p.id and p.active = 1
            ) 
        ORDER BY u.id
		");
    }
}
