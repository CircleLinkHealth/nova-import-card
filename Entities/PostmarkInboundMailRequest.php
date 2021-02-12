<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

class PostmarkInboundMailRequest
{
    // public ?array $Attachments;
    // public ?string $Bcc;
    // public ?array $BccFull;
    public ?string $Cc;
    // public ?array $CcFull;
    public ?string $From;
    // public ?array $FromFull;
    // public ?string $FromName;
    // public ?array $Headers;
    public ?string $HtmlBody;
    // public ?string $MailboxHash;
    // public ?string $MailboxID;
    // public ?string $MessageStream;
    // public ?string $OriginalRecipient;
    public ?string $ReplyTo;
    // public ?string $StrippedTextReply;
    public ?string $Subject;
    // public ?string $Tag;
    public ?string $TextBody;
    public ?string  $To;
    // public ?array $ToFull;

    public function __construct(array $input)
    {
        $this->setFields($input, [
            'Cc',
            'From',
            'ReplyTo',
            'Subject',
            'TextBody',
            'To',
        ]);
    }

    private function setFields(array $input, array $fields)
    {
        foreach ($fields as $field) {
            if (isset($input[$field])) {
                $this->$field = $input[$field];
            }
        }
    }
}
