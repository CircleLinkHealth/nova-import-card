<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\AppConfig;

use CircleLinkHealth\Core\Entities\AppConfig;

class WhoCanBubbleChat
{
    const CARE_AMBASSADOR             = 'care-ambassadors';
    const CARE_COACHES                = 'care-coaches';
    const ROLE_ALLOWED_TO_BUBBLE_CHAT = 'role_allowed_bubble_chat';

    public static function rolesAllowedBubbleChat()
    {
        $rolesAllowedBubbleChat = AppConfig::pull(self::ROLE_ALLOWED_TO_BUBBLE_CHAT, []);

        if (is_string($rolesAllowedBubbleChat)) {
            return [$rolesAllowedBubbleChat];
        }

        return $rolesAllowedBubbleChat;
    }
}
