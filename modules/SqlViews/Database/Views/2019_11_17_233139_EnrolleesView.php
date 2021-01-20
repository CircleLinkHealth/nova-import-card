<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\SqlViews\BaseSqlView;

class EnrolleesView extends BaseSqlView
{
    /**
     * Create the sql view.
     */
    public function createSqlView(): bool
    {
        return \DB::statement("
        CREATE VIEW {$this->getViewName()}
        AS
        SELECT e.*,
        u.display_name AS provider_name,
        u2.display_name as care_ambassador_name,
        p.display_name AS practice_name,
        pi.sex as provider_sex,
        pi.pronunciation as provider_pronunciation,
        IF (eil.counts > 0 OR uil.counts > 0, true, false) as invited
        
FROM enrollees AS e
LEFT JOIN users AS u ON u.id=e.provider_id
LEFT JOIN users AS u2 ON u2.id=e.care_ambassador_user_id
LEFT JOIN practices AS p ON p.id=e.practice_id
LEFT JOIN provider_info AS pi ON u.id=pi.user_id
LEFT JOIN (SELECT COUNT(*) as counts, invitationable_id from enrollables_invitation_links GROUP BY invitationable_id) as eil on e.id=eil.invitationable_id
LEFT JOIN (SELECT COUNT(*) as counts, invitationable_id from enrollables_invitation_links GROUP BY invitationable_id) as uil on e.user_id=uil.invitationable_id

WHERE p.active = 1
        ");
    }

    /**
     * Get the name of the sql view.
     */
    public function getViewName(): string
    {
        return 'enrollees_view';
    }
}
