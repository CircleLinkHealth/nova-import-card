<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Services\PhiMail\Incoming\Handlers;

use CircleLinkHealth\SharedModels\Entities\DirectMailMessage;
use CircleLinkHealth\SharedModels\Services\PhiMail\Incoming\IncomingDMMimeHandlerInterface;

abstract class BaseHandler implements IncomingDMMimeHandlerInterface
{
    /**
     * @var string
     */
    protected $attachmentData;
    /**
     * @var DirectMailMessage
     */
    protected $dm;

    public function __construct(DirectMailMessage &$dm, string $attachmentData)
    {
        $this->dm             = $dm;
        $this->attachmentData = $attachmentData;
    }
}
