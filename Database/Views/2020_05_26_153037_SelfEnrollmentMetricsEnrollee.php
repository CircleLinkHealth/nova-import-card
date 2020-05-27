<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\SqlViews\BaseSqlView;

class SelfEnrollmentMetricsEnrollee extends BaseSqlView
{
    /**
     * Create the sql view.
     */
    public function createSqlView(): bool
    {
        return \DB::statement("
        CREATE VIEW {$this->getViewName()}
        AS
        SELECT
        i.manually_expired,
        i.batch_id
        FROM
        enrollables_invitation_links i
        left join enrollment_invitations_batches b on i.batch_id = b.id
      ");
    }

    /**
     * Get the name of the sql view.
     */
    public function getViewName(): string
    {
        return 'self_enrollment_metrics_enrollee';
    }
}
