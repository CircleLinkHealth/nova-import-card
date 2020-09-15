<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\SqlViews\BaseSqlView;

class UnresolvedPostmarkCallbackView extends BaseSqlView
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
        upc.postmark_id,
        upc.user_id as patient_user_id,
        c.id
        
        FROM
            unresolved_postmark_callbacks upc
            
            left join calls c on upc.user_id = c.inbound_cpm_id
    
      ");
    }

    /**
     * Get the name of the sql view.
     */
    public function getViewName(): string
    {
        return 'unresolved_postmark_callback_view';
    }
}
