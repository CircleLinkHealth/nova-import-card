<?php


namespace CircleLinkHealth\SelfEnrollment\Services;


use Illuminate\Support\Facades\Http;

class NotifyEnrollableSurveyCompletedService
{
    private function getUrlInProvider(string $url)
    {
        return rtrim(config('core.apps.cpm-provider.url'), '/')."/$url";
    }

    public function makeRequestToProviderApp(string $url, int $enrolleeId)
    {
        return Http::get($this->getUrlInProvider($url), [
            'enrolleeId' => $enrolleeId
        ]);
    }
}