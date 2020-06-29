<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Listeners;

use App\Providers\CpmEventServiceProvider;
use Swift_Events_SendEvent;

/**
 * This listener is used on Swift Mailer events.
 * Listener is registered in {@link CpmEventServiceProvider::boot()}.
 */
class PostmarkAddSmtpIdOnHeader implements \Swift_Events_SendListener
{
    public function beforeSendPerformed(Swift_Events_SendEvent $evt)
    {
        $evt->getMessage()->getHeaders()->addTextHeader('X-PM-Metadata-smtp-id', $evt->getMessage()->getId());
    }

    public function sendPerformed(Swift_Events_SendEvent $evt)
    {
        // TODO: Implement sendPerformed() method.
    }
}
