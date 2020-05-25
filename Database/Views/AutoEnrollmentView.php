<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\SqlViews\BaseSqlView;

class AutoEnrollmentView extends BaseSqlView
{
    public function createSqlView(): bool
    {
        return \DB::statement("
        CREATE VIEW {$this->getViewName()}
        SELECT *
        FROM enrollables_invitation_links;
      ");
    }

    public function getViewName(): string
    {
        return 'auto_enrollment_view';
    }
}
