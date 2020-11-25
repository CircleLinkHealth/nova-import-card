<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Entities;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PostmarkInboundCallbackRequest
{
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
            'Cancel/Withdraw Reason:',
            'Msg:',
        ];
    }

    /**
     * @return array|void
     */
    public function run(string $inboundCallback, int $postmarkId)
    {
        $inboundCallbackArray = $this->getArrayFromStringWithBreaks($inboundCallback, $postmarkId);

        if (count($inboundCallbackArray) > 30) {
//            I dont see any other way in current code to distinguish this. A usual email has 19 - 22 count in Production
            sendSlackMessage('#carecoach_ops_alerts', "[$postmarkId] in postmark_inbound_mail, has lot of emails in body. Probably it is the daily callback
            report.");
            exit;
        }

        return  $this->arrayWithKeys($inboundCallbackArray);
    }

    /**
     * @return array
     */
    private function arrayWithKeys(Collection $inboundCallback)
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

    /**
     * @return \Collection|Collection|void
     */
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

            return null;
        }
    }
}
