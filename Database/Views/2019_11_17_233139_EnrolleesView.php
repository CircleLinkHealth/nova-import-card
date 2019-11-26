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
        pi.pronunciation as provider_pronunciation
        
FROM enrollees AS e
LEFT JOIN users AS u ON u.id=e.provider_id
LEFT JOIN users AS u2 ON u2.id=e.care_ambassador_user_id
LEFT JOIN practices AS p ON p.id=e.practice_id
LEFT JOIN provider_info AS pi ON u.id=pi.user_id

WHERE NOT LOWER(u.display_name) IN (
SELECT name FROM enrollee_custom_filters  ecf
LEFT JOIN practice_enrollee_filters pef ON ecf.id=pef.filter_id
WHERE ecf.type = 'provider' AND
pef.practice_id = e.practice_id AND
pef.include = 1)

AND NOT (LOWER(e.primary_insurance) IN (SELECT name FROM enrollee_custom_filters WHERE enrollee_custom_filters.type = 'insurance') AND
e.secondary_insurance IS NULL AND
e.tertiary_insurance IS NULL);
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
