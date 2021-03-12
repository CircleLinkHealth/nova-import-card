<?php

namespace CircleLinkHealth\EpicSso\Events;

class EpicSsoLoginEvent
{
    public int $epicUserId;

    public function __construct(int $epicUserId)
    {
        $this->epicUserId = $epicUserId;
    }
}
