<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts;

use App\User;

interface DirectMail
{
    /**
     * @return mixed
     */
    public function receive();

    /**
     * @param $outboundRecipient
     * @param null $binaryAttachmentFilePath
     * @param null $binaryAttachmentFileName
     * @param null $ccdaAttachmentPath
     * @param User|null $patient
     * @param null $body
     * @param null $subject
     *
     * @return mixed
     */
    public function send(
        $outboundRecipient,
        $binaryAttachmentFilePath = null,
        $binaryAttachmentFileName = null,
        $ccdaAttachmentPath = null,
        User $patient = null,
        $body = null,
        $subject = null
    );
}
