<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\SqlViews\BaseSqlView;

class PatientsBhiChargeableView extends BaseSqlView
{
    /**
     * Create the sql view.
     */
    public function createSqlView(): bool
    {
        return \DB::statement("
        CREATE VIEW {$this->getViewName()}
        AS
        SELECT u.id
        FROM users u
        JOIN patient_info p on u.id = p.user_id
        WHERE u.deleted_at is null
            AND p.ccm_status = 'enrolled'
	        AND (p.consent_date >= '2018-07-23' or exists (select * from notes n where n.patient_id = u.id and n.type = 'Consented to BHI'))
	        AND EXISTS (
	            SELECT *
	        	FROM ccd_problems ccd
		        JOIN cpm_problems cpm on ccd.cpm_problem_id = cpm.id
        		WHERE ccd.deleted_at is null
        		AND ccd.is_monitored = true
        		AND cpm.is_behavioral = true
        		AND patient_id = u.id
	        )
	        AND
	        EXISTS (
		        SELECT *
		        FROM practices p
		        JOIN chargeables c on p.id = c.chargeable_id
		        JOIN chargeable_services cs on cs.id = c.chargeable_service_id
		        WHERE c.chargeable_type = 'App\\\\Practice'
		        AND code = 'CPT 99484'
		        AND u.program_id = p.id
		        AND p.active = true
            )
        ORDER BY u.id
      ");
    }

    /**
     * Get the name of the sql view.
     */
    public function getViewName(): string
    {
        return 'patients_bhi_chargeable_view';
    }
}
