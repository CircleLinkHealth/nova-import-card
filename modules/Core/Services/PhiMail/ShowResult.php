<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Services\PhiMail;

class ShowResult
{
    /**
     * Array of information about any available attachemnts (part 0 only).
     *
     * @var AttachmentInfo[]|null
     */
    public $attachmentInfo;
    /**
     * The message part content data.
     *
     * @var string
     */
    public $data;
    /**
     * Optional filename for this message part, if specified by sender
     * (part > 0 only).
     *
     * @var string|null
     */
    public $filename;
    /**
     * Array of message header lines (part 0 only).
     *
     * @var string[]|null
     */
    public $headers;
    /**
     * The number of bytes of data in this message part.
     *
     * @var int
     */
    public $length;
    /**
     * The MIME type of the message part.
     *
     * @var string
     */
    public $mimeType;

    /**
     * The message part returned: 0..n-1.
     *
     * @var int
     */
    public $partNum;

    public function __construct($p, $h, $f, $m, $l, $d, $ai)
    {
        $this->partNum        = $p;
        $this->headers        = $h;
        $this->filename       = $f;
        $this->mimeType       = $m;
        $this->length         = $l;
        $this->data           = $d;
        $this->attachmentInfo = $ai;
    }
}
