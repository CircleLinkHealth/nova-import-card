<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\PhiMail\Incoming\Handlers;

use App\Services\PhiMail\Incoming\IncomingDMMimeHandlerInterface;
use CircleLinkHealth\SharedModels\Entities\DirectMailMessage;

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
