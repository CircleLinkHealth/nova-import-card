<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SelfEnrollment\Services;

use Illuminate\Support\Facades\Http;

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
        return rtrim(config('core.apps.cpm-provider.url'), '/')."/$url";
    }
}
