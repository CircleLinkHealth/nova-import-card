<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

use CircleLinkHealth\SharedModels\DTO\PostmarkCallbackInboundData;
use CircleLinkHealth\SharedModels\Exceptions\CannotParseCallbackException;
use CircleLinkHealth\SharedModels\Exceptions\DailyCallbackReportException;
use Illuminate\Support\Str;

class PostmarkInboundCallbackRequest
{
    const INBOUND_CALLBACK_REQUEST_MAX_ARRAY_ITEMS = 16;

    /**
     * @return string[]
     */
    public function getKeys(): array
    {
        return [
            'For:',
            'From:',
            'Phone:',
            'Ptn:',
            'Cancel/Withdraw Reason:',
            'Msg:',
            'Primary:',
            'Msg ID:',
            'IS Rec #:',
            'Clr ID:',
            'Taken:',
            'Msg:',
        ];
    }

    /**
     * @throws CannotParseCallbackException|DailyCallbackReportException
     */
    public function process(string $inboundCallback, int $postmarkId): PostmarkCallbackInboundData
    {
        $inboundCallbackArray = $this->getArrayFromStringWithBreaks($inboundCallback);
        if (count($inboundCallbackArray) > self::INBOUND_CALLBACK_REQUEST_MAX_ARRAY_ITEMS) {
            throw new DailyCallbackReportException();
        }

        $inboundDataArray = $this->inboundDataInArray($inboundCallbackArray);
        if (empty($inboundDataArray)) {
            sendSlackMessage('#carecoach_ops_alerts', "Email body is empty for inbound_postmark_mail [$postmarkId]");
        }

        return new PostmarkCallbackInboundData($inboundDataArray);
    }

    /**
     * @throws CannotParseCallbackException
     */
    private function getArrayFromStringWithBreaks(string $inboundCallback): array
    {
        $array = explode("\n", $inboundCallback);

        try {
            return collect($array)
                ->transform(function ($item) {
                    return trim($item);
                })
                ->toArray();
        } catch (\Exception $e) {
            throw new CannotParseCallbackException($e->getMessage());
        }
    }

    private function inboundDataInArray(array $inboundCallback): array
    {
        $coll             = collect($inboundCallback);
        $callbackDataKeys = $this->getKeys();
        $callbackData     = [];

        foreach ($callbackDataKeys as $callbackDataKey) {
            $coll->each(function ($callbackDataItem) use ($callbackDataKey, &$callbackData) {
                if (Str::contains($callbackDataItem, $callbackDataKey)) {
                    $key = trim($callbackDataKey, ':');
                    $value = trim(trim(substr($callbackDataItem, strpos($callbackDataItem, $callbackDataKey) + strlen($callbackDataKey)), '|'));
                    $callbackData[$key] = $value;
                }
            });
        }

        return $callbackData;
    }
}
