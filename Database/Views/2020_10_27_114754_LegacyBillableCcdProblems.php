<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\SqlViews\BaseSqlView;

class LegacyBillableCcdProblems extends BaseSqlView
{
    /**
     * Create the sql view.
     */
    public function createSqlView(): bool
    {
        return \DB::statement("
        CREATE VIEW {$this->getViewName()}
        AS
        Select
ccd.*,
IF ((cpm.is_behavioral = 0 OR cpm.name = 'Depression' OR cpm.name='Dementia' ), 1, 0) as is_ccm,
IF (cpm.is_behavioral= 1 OR cpm.name = 'Depression' OR cpm.name='Dementia', 1, 0) as is_bhi,

EXISTS(
SELECT *
FROM pcm_problems pcm
WHERE
(pcm.practice_id = l.practice_id OR pcm.practice_id=u.program_id)
AND pcm.code_type = 'ICD10'
AND (pcm.description LIKE CONCAT('%',ccd.name,'%') OR pcm.code=pc.code)
)

as is_pcm,

EXISTS(
SELECT *
FROM rpm_problems rpm
WHERE
(rpm.practice_id = l.practice_id OR rpm.practice_id=u.program_id)
AND rpm.code_type = 'ICD10'
AND (rpm.description LIKE CONCAT('%',ccd.name,'%') OR rpm.code=pc.code)
)

as is_rpm

from ccd_problems ccd
left join patient_info pi on pi.user_id=ccd.patient_id
left join locations l on pi.preferred_contact_location=l.id
left join users u on ccd.patient_id=u.id
left join cpm_problems cpm on cpm.id=ccd.cpm_problem_id
left join problem_codes pc on ccd.id=pc.problem_id
      ");
    }

    /**
     * Get the name of the sql view.
     */
    public function getViewName(): string
    {
        return 'legacy_billable_ccd_problems';
    }
}
