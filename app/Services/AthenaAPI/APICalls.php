<?php

namespace App\Services\AthenaAPI;


class APICalls
{
    protected $api;
    protected $key;
    protected $secret;
    protected $version;

    public function __construct()
    {
        $this->key = env('ATHENA_KEY');
        $this->secret = env('ATHENA_SECRET');
        $this->version = env('ATHENA_VERSION');

        $this->api = new APIConnection($this->version, $this->key, $this->secret, env('ATHENA_CLH_PRACTICE_ID'));
    }

    public function getCcd($patientId, $practiceId, $departmentId = 1)
    {
        $response = $this->api->GET("patients/{$patientId}/ccda", [
            'patientid' => $patientId,
            'practiceid' => $practiceId,
            'departmentid' => $departmentId,
            'purpose' => 'internal',
            'xmloutput' => true,
        ]);

        return $this->response($response);
    }

    public function getBookedAppointments($practiceId, $startDate, $endDate, $showInsurance = false, $limit = 1000, $departmentId = 1)
    {
        $response = $this->api->GET("{$practiceId}/appointments/booked", [
            'practiceid' => $practiceId,
            'startdate' => $startDate,
            'enddate' => $endDate,
            'departmentid' => $departmentId,
            'showinsurance' => $showInsurance,
            'limit' => $limit,
        ]);

        return $this->response($response);
    }

    /**
     * @param $url
     * @return bool|mixed
     */
    public function getNextPage($url)
    {
        return $this->api->GET($url);
    }

    private function response($response)
    {
        //check for errors
        if (isset($response['error'])) {
            \Log::alert(__METHOD__ . __LINE__ . 'Response logged below');
            \Log::error($response);

            abort(400, json_encode($response));
        }

        return $response;
    }
}