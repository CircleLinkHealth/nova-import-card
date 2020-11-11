<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Timetracking\Services;

use CircleLinkHealth\TimeTracking\Entities\OfflineActivityTimeRequest;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class TimeTrackerServerService
{
    /**
     * Send a request to the time-tracking server to increment the start-time by the duration of the offline-time activity (in seconds).
     */
    public function syncOfflineTime(OfflineActivityTimeRequest $request)
    {
        $client = new Client();

        $url = config('services.ws.server-url').'/'.$request->requester_id.'/'.$request->patient_id;

        try {
            $res = $client->put(
                $url,
                [
                    'form_params' => [
                        'chargeable_service_id'   => $request->chargeable_service_id,
                        'chargeable_service_code' => $request->chargeableService->code,
                        'chargeable_service_name' => $request->chargeableService->display_name,
                        'duration_seconds'        => $request->duration_seconds,
                    ],
                ]
            );
            $status = $res->getStatusCode();
            $body   = $res->getBody();
            if (200 == $status) {
                Log::info($body);
            } else {
                Log::critical($body);
            }
        } catch (\Exception $ex) {
            Log::critical($ex);
        }
    }
}
