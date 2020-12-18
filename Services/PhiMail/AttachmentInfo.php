<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Services\PhiMail;

class AttachmentInfo
{
    /** @var string */
    public $description;

    /** @var string */
    public $filename;
    /** @var string */
    public $mimeType;

    public function __construct($filename, $mimeType, $description)
    {
        $this->filename    = $filename;
        $this->mimeType    = $mimeType;
        $this->description = $description;
    }
}
