<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SelfEnrollment\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotifyEnrollableSurveyCompletedService
{
    public function makeRequestToProviderApp(string $url, int $enrolleeId)
    {
        return Http::get($this->getUrlInProvider($url), [
            'enrolleeId' => $enrolleeId,
        ]);
    }

    private function getUrlInProvider(string $url)
    {
        $result = rtrim(config('core.apps.cpm-provider.url'), '/')."/$url";
        Log::debug("Ready to call url: $result");

        return $result;
    }
}
