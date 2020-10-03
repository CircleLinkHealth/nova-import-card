<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Actions;

use App\DirectMailMessage;
use App\Services\PhiMail\IncomingMessageHandler;
use CircleLinkHealth\Customer\Entities\Media;

class ReprocessDirectMailAttachments
{
    private IncomingMessageHandler $handler;

    public function __construct(IncomingMessageHandler $handler)
    {
        $this->handler = $handler;
    }

    public function reprocess(int $directMailMessageId)
    {
        $dm = $this->fetchDm($directMailMessageId);

        if ( ! $dm) {
            return;
        }

        $dm->media
            ->each(function (Media $media) use ($dm) {
                $this->handler->handleMessageAttachment($dm, $media->mime_type, $media->getFile());
            });

        $this->handler->processCcdas($dm);
    }

    private function fetchDm(int $directMailMessageId)
    {
        return DirectMailMessage::with('media')
            ->findOrFail($directMailMessageId);
    }
}
