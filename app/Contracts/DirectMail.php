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
     * @param $binaryAttachmentFilePath
     * @param $binaryAttachmentFileName
     * @param null      $ccdaAttachmentPath
     * @param User|null $patient
     *
     * @return mixed
     */
    public function send(
        $outboundRecipient,
        $binaryAttachmentFilePath,
        $binaryAttachmentFileName,
        $ccdaAttachmentPath = null,
        User $patient = null
    );
}
