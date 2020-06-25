<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\SqlViews\BaseSqlView;

class PracticePatientsView extends BaseSqlView
{
    /**
     * Create the sql view.
     */
    public function createSqlView(): bool
    {
        return \DB::statement("
        CREATE VIEW {$this->getViewName()}
        AS
select u.id, u.first_name, u.last_name, u.suffix, u.display_name, u.city, u.state, u.program_id, pi.ccm_status, cp.status, pi.preferred_contact_language
from users u
inner join practice_role_user pru on u.id = pru.user_id and pru.program_id = u.program_id
inner join patient_info pi on u.id = pi.user_id and pi.is_awv = 0
left join care_plans cp on u.id = cp.user_id
where pru.role_id = 2 and u.deleted_at is null;
      ");
    }

    /**
     * Get the name of the sql view.
     */
    public function getViewName(): string
    {
        return 'practice_patients_view';
    }
}
