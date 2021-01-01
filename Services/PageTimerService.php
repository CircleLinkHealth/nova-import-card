<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Services;

use Carbon\Carbon;
use CircleLinkHealth\SharedModels\DTO\CreatePageTimerParams;
use CircleLinkHealth\SharedModels\Entities\PageTimer;
use Illuminate\Support\Facades\Log;

class PageTimerService
{
    public function createPageTimer(CreatePageTimerParams $params): PageTimer
    {
        $activity = $params->getActivity();

        $duration = $activity['duration'];

        $startTime = Carbon::createFromFormat('Y-m-d H:i:s', $activity['start_time']);
        $endTime   = $startTime->copy()->addSeconds($duration);
        if (isset($activity['end_time'])) {
            try {
                $endTime = Carbon::createFromFormat('Y-m-d H:i:s', $activity['end_time']);
            } catch (\Throwable $e) {
                Log::warning('Could not read activity[end_time]: '.$e->getMessage());
            }
        }

        $csId = null;
        if (isset($activity['chargeable_service_id'])) {
            $csId = -1 === $activity['chargeable_service_id'] ? null : $activity['chargeable_service_id'];
        }

        $programId = $params->getProgramId();
        if (empty($programId)) {
            $programId = null;
        }

        $pageTimer                        = new PageTimer();
        $pageTimer->redirect_to           = $params->getRedirectLocation();
        $pageTimer->billable_duration     = $duration;
        $pageTimer->duration              = $duration;
        $pageTimer->duration_unit         = 'seconds';
        $pageTimer->patient_id            = $params->getPatientId();
        $pageTimer->enrollee_id           = empty($activity['enrolleeId']) ? null : $activity['enrolleeId']; //0 is null
        $pageTimer->provider_id           = $params->getProviderId();
        $pageTimer->chargeable_service_id = $csId;
        $pageTimer->start_time            = $startTime->toDateTimeString();
        $pageTimer->end_time              = $endTime->toDateTimeString();
        $pageTimer->url_full              = $activity['url'];
        $pageTimer->url_short             = $activity['url_short'];
        $pageTimer->program_id            = $programId;
        $pageTimer->ip_addr               = $params->getIpAddr();
        $pageTimer->activity_type         = $activity['name'];
        $pageTimer->title                 = $activity['title'];
        $pageTimer->user_agent            = $params->getUserAgent();
        $pageTimer->save();

        return $pageTimer;
    }
}
