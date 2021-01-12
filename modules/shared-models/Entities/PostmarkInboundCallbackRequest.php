<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

use CircleLinkHealth\Customer\DTO\PostmarkCallbackInboundData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PostmarkInboundCallbackRequest
{
    const INBOUND_CALLBACK_DAILY_REPORT            = 'Inbound callback daily report, will not process';
    const INBOUND_CALLBACK_REQUEST_MAX_ARRAY_ITEMS = 16;

    /**
     * @return string[]
     */
    public function getKeys()
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
     * @throws \Exception
     * @return PostmarkCallbackInboundData
     */
    public function run(string $inboundCallback, int $postmarkId)
    {
        $inboundCallbackArray = $this->getArrayFromStringWithBreaks($inboundCallback, $postmarkId);

        if (count($inboundCallbackArray) > self::INBOUND_CALLBACK_REQUEST_MAX_ARRAY_ITEMS) {
            throw new \Exception(self::INBOUND_CALLBACK_DAILY_REPORT);
        }

        $inboundDataArray = $this->inboundDataInArray($inboundCallbackArray);

        if (empty($inboundDataArray)) {
            sendSlackMessage('#carecoach_ops_alerts', "Email body is empty for inbound_postmark_mail [$postmarkId]");
        }

        return new PostmarkCallbackInboundData($inboundDataArray);
    }

    private function getArrayFromStringWithBreaks(string $inboundCallback, int $postmarkId)
    {
        $array = explode("\n", $inboundCallback);

        try {
            return collect($array)->transform(function ($item) {
                return trim($item);
            });
        } catch (\Exception $e) {
            $message = $e->getMessage();
            Log::error("Inbound Callback could not be parsed. id:$postmarkId. [$message]");
            sendSlackMessage('#carecoach_ops_alerts', "Inbound Callback could not be parsed. id:$postmarkId. [$message]");

            return null;
        }
    }

    /**
     * @return array
     */
    private function inboundDataInArray(Collection $inboundCallback)
    {
        $callbackDataKeys = $this->getKeys();

        $callbackData = [];
        foreach ($callbackDataKeys as $callbackDataKey) {
            $inboundCallback->map(function ($callbackDataItem) use ($callbackDataKey, &$callbackData) {
                if (Str::contains($callbackDataItem, $callbackDataKey)) {
                    $callbackData[trim($callbackDataKey, ':')] = trim(trim(substr($callbackDataItem, strpos($callbackDataItem, $callbackDataKey) + strlen($callbackDataKey)), '|'));
                }
            });
        }

        return $callbackData;
    }
}
