<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Services\PhiMail\Incoming;

use CircleLinkHealth\SharedModels\Entities\DirectMailMessage;

interface IncomingDMMimeHandlerInterface
{
    public function __construct(DirectMailMessage &$dm, string $attachmentData);

    public function handle();
}
