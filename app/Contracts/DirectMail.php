<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts;

use CircleLinkHealth\Customer\Entities\User;

interface DirectMail
{
    /**
     * @param mixed|null $dmUserAddress
     *
     * @return mixed
     */
    public function receive($dmUserAddress = null);

    /**
     * @param $outboundRecipient
     * @param null       $binaryAttachmentFilePath
     * @param null       $binaryAttachmentFileName
     * @param null       $ccdaContents
     * @param null       $body
     * @param null       $subject
     * @param mixed|null $dmUserAddress
     *
     * @return mixed
     */
    public function send(
        $outboundRecipient,
        $binaryAttachmentFilePath = null,
        $binaryAttachmentFileName = null,
        $ccdaContents = null,
        User $patient = null,
        $body = null,
        $subject = null,
        $dmUserAddress = null
    );
}
