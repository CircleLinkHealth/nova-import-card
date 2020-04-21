<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\PhiMail\Incoming;

use App\DirectMailMessage;
use App\Services\PhiMail\Incoming\Handlers\Pdf;
use App\Services\PhiMail\Incoming\Handlers\Plain;
use App\Services\PhiMail\Incoming\Handlers\Unknown;
use App\Services\PhiMail\Incoming\Handlers\XML;
use App\Services\PhiMail\ShowResult;
use Illuminate\Support\Str;

class Factory
{
    /**
     * We support attachments if their MIME Type contains any of the following wildcards.
     *
     * Example: text/pdf, application/pdf
     */
    const SUPPORTED_MIME_TYPE_WILDCARDS = [
        'plain',
        'xml',
        'pdf',
    ];

    /**
     * Call this method if the message contains attachments that are not in SUPPORTED_MIME_TYPE_WILDCARDS.
     */
    const UNKNOWN_MIME_HANDLER_METHOD_NAME = 'handleUnknownMimeType';

    /**
     * Handles the message's attachments.
     *
     * @return
     */
    public static function create(DirectMailMessage &$dm, ShowResult $showRes): IncomingDMMimeHandlerInterface
    {
        $static = new static();

        return $static->{$static->getHandlerMethodName($showRes->mimeType)}($dm, $showRes->data);
    }

    private function getHandlerMethodName(string $mimeType)
    {
        foreach (self::SUPPORTED_MIME_TYPE_WILDCARDS as $supportedMime) {
            if (Str::contains($mimeType, $supportedMime)) {
                return 'handle'.Str::camel($supportedMime).'MimeType';
            }
        }

        return self::UNKNOWN_MIME_HANDLER_METHOD_NAME;
    }

    private function handlePdfMimeType(DirectMailMessage &$dm, string $attachmentData): IncomingDMMimeHandlerInterface
    {
        return new Pdf($dm, $attachmentData);
    }

    private function handlePlainMimeType(DirectMailMessage &$dm, string $attachmentData): IncomingDMMimeHandlerInterface
    {
        return new Plain($dm, $attachmentData);
    }

    private function handleUnknownMimeType(
        DirectMailMessage &$dm,
        string $attachmentData
    ): IncomingDMMimeHandlerInterface {
        return new Unknown($dm, $attachmentData);
    }

    private function handleXmlMimeType(DirectMailMessage &$dm, string $attachmentData): IncomingDMMimeHandlerInterface
    {
        return new XML($dm, $attachmentData);
    }
}
