<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\TimeTracking\Services;

use CircleLinkHealth\SharedModels\Entities\OfflineActivityTimeRequest;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class TimeTrackerServerService
{
    public function clearCache(int $patientId)
    {
        $url = config('services.ws.server-url').'/cache/'.$patientId.'/clear';
        $this->sendRequest($url, []);
    }

    /**
     * Send a request to the time-tracking server to increment the start-time by the duration of the offline-time activity (in seconds).
     */
    public function syncOfflineTime(OfflineActivityTimeRequest $request)
    {
        $url = config('services.ws.server-url').'/'.$request->requester_id.'/'.$request->patient_id;
        $this->sendRequest($url, [
            'chargeable_service_id'   => $request->chargeable_service_id,
            'chargeable_service_code' => $request->chargeableService->code,
            'chargeable_service_name' => $request->chargeableService->display_name,
            'duration_seconds'        => $request->duration_seconds,
        ]);
    }

    private function sendRequest(string $url, array $params)
    {
        $client = new Client();
        try {
            $res = $client->put(
                $url,
                [
                    'form_params' => $params,
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
