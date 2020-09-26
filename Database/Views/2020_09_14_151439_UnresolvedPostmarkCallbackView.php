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
        $notEnrolled         = \App\Services\Postmark\PostmarkInboundCallbackMatchResults::NOT_ENROLLED;
        $queuedAndUnassigned = \App\Services\Postmark\PostmarkInboundCallbackMatchResults::QUEUED_AND_UNASSIGNED;
        $withdrawRequest     = \App\Services\Postmark\PostmarkInboundCallbackMatchResults::WITHDRAW_REQUEST;
        $noNameMatch         = \App\Services\Postmark\PostmarkInboundCallbackMatchResults::NO_NAME_MATCH;
        $noNameSelfMatch     = \App\Services\Postmark\PostmarkInboundCallbackMatchResults::NO_NAME_MATCH_SELF;

        return \DB::statement("
        CREATE VIEW {$this->getViewName()} AS
        SELECT
        upc.postmark_id as postmark_id,
        upc.user_id as matched_user_id,
        p.body as inbound_data,
        
        CASE WHEN upc.unresolved_reason = '$notEnrolled' THEN 'Not Enrolled'
        WHEN upc.unresolved_reason = '$queuedAndUnassigned' THEN 'Enrollment Queue / CA unassigned'
        WHEN upc.unresolved_reason = '$withdrawRequest' THEN 'Withdraw Request'
        WHEN upc.unresolved_reason = '$noNameMatch' THEN 'Name not matched'
        WHEN upc.unresolved_reason = '$noNameSelfMatch' THEN 'Pt. SELF not matched'
        END as unresolved_reason,
        
        upc.suggestions as other_possible_matches,
        
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
            left join postmark_inbound_mail p on upc.postmark_id = p.id
         
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
