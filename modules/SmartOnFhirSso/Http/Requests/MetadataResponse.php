<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SmartOnFhirSso\Http\Requests;

use Illuminate\Support\Facades\Log;

class MetadataResponse
{
    public ?string $authorizeUrl;
    public ?string $tokenUrl;

    public function __construct(array $arr)
    {
        try {
            $urlsArray = $this->getUrlsArray($arr);
            $this->setFields($urlsArray);
        } catch (\Exception $e) {
            Log::error($e);
            $this->authorizeUrl = null;
            $this->tokenUrl     = null;
        }
    }

    private function getUrlsArray($arr): array
    {
        if ( ! isset($arr['rest']) || empty($arr['rest'])) {
            return [];
        }

        $temp = reset($arr['rest']);
        if ( ! isset($temp['security']) || empty($temp['security'])) {
            return [];
        }

        $temp = $temp['security'];
        if ( ! isset($temp['extension']) || empty($temp['extension'])) {
            return [];
        }

        $temp = reset($temp['extension']);
        if ( ! isset($temp['extension']) || empty($temp['extension'])) {
            return [];
        }

        return $temp['extension'];
    }

    private function setFields($urlsArray): void
    {
        foreach ($urlsArray as $item) {
            switch ($item['url']) {
                case 'authorize':
                    $this->authorizeUrl = $item['valueUri'];
                    break;
                case 'token':
                    $this->tokenUrl = $item['valueUri'];
                    break;
                default:
                    break;
            }
        }
    }
}
