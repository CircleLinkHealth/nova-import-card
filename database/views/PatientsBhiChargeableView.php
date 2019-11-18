<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\Contracts\SqlViewInterface;

class PatientsBhiChargeableView implements SqlViewInterface
{
    /**
     * Drop and create Sql Views.
     *
     * @return mixed
     */
    public static function dropAndCreate()
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
