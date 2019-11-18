<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\SqlViews\Contracts\SqlViewInterface;

class PatientsCcmView implements SqlViewInterface
{
    /**
     * Drop and create Sql Views.
     *
     * @return mixed
     */
    public static function dropAndCreate()
    {
        $viewName = 'patients_ccm_view';
        \DB::statement("DROP VIEW IF EXISTS ${viewName}");
        \DB::statement("
        CREATE VIEW ${viewName}
        AS
        SELECT DISTINCT u.id
        FROM users u
        JOIN ccd_problems ccd on u.id = ccd.patient_id
        JOIN cpm_problems cpm on ccd.cpm_problem_id = cpm.id
        WHERE u.deleted_at is null and ccd.deleted_at is null and ccd.is_monitored = 1 and cpm.is_behavioral = 0
        ORDER BY u.id;
		");
    }
}
