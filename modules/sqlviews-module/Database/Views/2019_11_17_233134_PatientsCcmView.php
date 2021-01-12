<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\SqlViews\BaseSqlView;

class PatientsCcmView extends BaseSqlView
{
    /**
     * Create the sql view.
     */
    public function createSqlView(): bool
    {
        return \DB::statement("
        CREATE VIEW {$this->getViewName()}
        AS
        SELECT DISTINCT u.id
        FROM users u
        JOIN ccd_problems ccd on u.id = ccd.patient_id
        JOIN cpm_problems cpm on ccd.cpm_problem_id = cpm.id
        WHERE u.deleted_at is null and ccd.deleted_at is null and ccd.is_monitored = 1 and cpm.is_behavioral = 0
        ORDER BY u.id;
      ");
    }

    /**
     * Get the name of the sql view.
     */
    public function getViewName(): string
    {
        return 'patients_ccm_view';
    }
}
