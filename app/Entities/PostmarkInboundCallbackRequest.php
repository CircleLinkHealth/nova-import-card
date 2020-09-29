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

    public function run(string $inboundCallback, int $postmarkId)
    {
        $stringToArray = $this->getArrayFromStringWithBreaks($inboundCallback, $postmarkId);

        return  $this->arrayWithKeys($stringToArray);
    }

    /**
     * @return array
     */
    private function arrayWithKeys(Collection $stringToArray)
    {
        $keys = $this->getKeys();

        $data = [];
        foreach ($keys as $key) {
            $stringToArray->map(function ($item) use ($key, &$data) {
                if (Str::contains($item, $key)) {
                    $data[trim($key, ':')] = trim(trim(substr($item, strpos($item, $key) + strlen($key)), '|'));
                }
            });
        }

        return $data;
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

            return;
        }
    }
}
