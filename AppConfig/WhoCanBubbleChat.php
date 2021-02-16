<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\AppConfig;

use CircleLinkHealth\Core\Entities\AppConfig;

class WhoCanBubbleChat
{
    const ROLES_ALLOWED_TO_BUBBLE_CHAT = 'roles_allowed_bubble_chat';

    public static function rolesAllowedBubbleChat(): ?array
    {
        $rolesAllowedBubbleChat = AppConfig::pull(self::ROLES_ALLOWED_TO_BUBBLE_CHAT, null);

        if ( ! empty($rolesAllowedBubbleChat) && is_string($rolesAllowedBubbleChat)) {
            return explode(',', collect($rolesAllowedBubbleChat)->first());
        }

        return [];
    }
}
