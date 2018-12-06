<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 11/17/2017
 * Time: 12:32 PM
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