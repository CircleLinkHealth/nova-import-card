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
        CREATE VIEW {$this->getViewName()} AS
        SELECT
        upc.postmark_id as postmark_id,
        upc.user_id as suggested_user_id,
        upc.unresolved_reason,
        upc.suggestions as other_user_suggestions,
        
        CASE WHEN c.created_at > upc.created_at
        AND c.sub_type = 'Call Back'
        THEN c.id
        END as call_id,
        
        CASE WHEN c.id IS NOT NULL
        AND c.created_at > upc.created_at
        AND c.sub_type = 'Call Back'
        THEN true
        ELSE false
        END as resolved
        
        FROM
            unresolved_postmark_callbacks upc
           
            left join calls c on upc.user_id = c.inbound_cpm_id
         
         WHERE 0 = (SELECT COUNT(c2.id)
             FROM calls c2
             WHERE c.inbound_cpm_id = c2.inbound_cpm_id
             AND c2.id < c.id)
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
