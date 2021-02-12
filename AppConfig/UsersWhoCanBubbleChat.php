<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\AppConfig;

use CircleLinkHealth\Core\Entities\AppConfig;

class UsersWhoCanBubbleChat
{
    const USER_IDS_TO_SHOW_CHAT_BUBBLE = 'user_ids_to_show_bubble_chat';

    public static function usersToShowBubbleChat()
    {
        $bubbleChatterIds = AppConfig::pull(self::USER_IDS_TO_SHOW_CHAT_BUBBLE, []);

        if ( ! empty($bubbleChatterIds)) {
            return explode(',', collect($bubbleChatterIds)->first());
        }

        return $bubbleChatterIds;
    }
}
