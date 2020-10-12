<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\Services\Postmark\PostmarkInboundCallbackMatchResults;
use CircleLinkHealth\SqlViews\BaseSqlView;

class UnresolvedPostmarkCallbackView extends BaseSqlView
{
    /**
     * Create the sql view.
     */
    public function createSqlView(): bool
    {
        $notEnrolled                 = PostmarkInboundCallbackMatchResults::NOT_ENROLLED;
        $queuedAndUnassigned         = PostmarkInboundCallbackMatchResults::QUEUED_AND_UNASSIGNED;
        $withdrawRequest             = PostmarkInboundCallbackMatchResults::WITHDRAW_REQUEST;
        $noNameMatch                 = PostmarkInboundCallbackMatchResults::MULTIPLE_MATCHES;
        $noNameSelfMatch             = PostmarkInboundCallbackMatchResults::NO_NAME_MATCH_SELF;
        $notConsentedAndCAUnassigned = PostmarkInboundCallbackMatchResults::NOT_CONSENTED_CA_UNASSIGNED;

        return \DB::statement("
        CREATE VIEW {$this->getViewName()} AS
        SELECT
        upc.postmark_id as postmark_id,
        upc.user_id as matched_user_id,
        p.body as inbound_data,
        upc.created_at as date,
        upc.manually_resolved,
        
        CASE WHEN upc.unresolved_reason = '$notEnrolled' THEN 'Not enrolled'
        WHEN upc.unresolved_reason = '$queuedAndUnassigned' THEN 'Self enrollment queue - CA unassigned'
        WHEN upc.unresolved_reason = '$withdrawRequest' THEN 'Withdraw request'
        WHEN upc.unresolved_reason = '$noNameMatch' THEN 'Multiple patients matched'
        WHEN upc.unresolved_reason = '$noNameSelfMatch' THEN 'Pt. SELF not matched'
        WHEN upc.unresolved_reason = '$notConsentedAndCAUnassigned' THEN 'Not consented - CA unassigned'
        END as unresolved_reason,
        
        upc.suggestions as other_possible_matches,
        
        CASE WHEN c.created_at >= upc.created_at
        AND c.sub_type = 'Call Back'
        THEN c.id
        END as call_id,
        
        CASE WHEN c.id IS NOT NULL
        AND c.created_at >= upc.created_at
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
