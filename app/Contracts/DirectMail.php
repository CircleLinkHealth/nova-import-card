<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts;

use App\User;

interface DirectMail
{
    public function receive();

    public function send(
        $outboundRecipient,
        $binaryAttachmentFilePath,
        $binaryAttachmentFileName,
        $ccdaAttachmentPath = null,
        User $patient = null
    );
}
