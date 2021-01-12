<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\PhiMail\Incoming;

use App\DirectMailMessage;

interface IncomingDMMimeHandlerInterface
{
    public function __construct(DirectMailMessage &$dm, string $attachmentData);

    public function handle();
}
