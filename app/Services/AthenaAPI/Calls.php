<?php

namespace App\Services\AthenaAPI;


class Calls
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

        $this->api = new Connection($this->version, $this->key, $this->secret, env('ATHENA_CLH_PRACTICE_ID'));
    }

    /**
     * Get a practise's book appointments for a date range
     * Dates are expected in mm/dd/yyyy format.
     *
     * @param $practiceId
     * @param $startDate
     * @param $endDate
     * @param bool $showInsurance
     * @param int $limit
     * @param int $departmentId
     *
     * @return mixed
     */
    public function getBookedAppointments(
        $practiceId,
        $startDate,
        $endDate,
        $departmentId,
        $showInsurance = false,
        $limit = 1000,
        $showCancelled = false
    ) {
        $this->api->setPracticeId($practiceId);

        $response = $this->api->GET("appointments/booked", [
            'practiceid'    => $practiceId,
            'startdate'     => $startDate,
            'enddate'       => $endDate,
            'departmentid'  => $departmentId,
            'showinsurance' => $showInsurance,
            'limit'         => $limit,
            'showcancelled' => $showCancelled,
        ]);

        return $this->response($response);
    }

    /**
     * Checks if the response contains errors. If it does, it logs the response and throws an Exception
     *
     * @throws \Exception
     *
     * @param $response
     *
     * @return mixed
     */
    private function response($response)
    {
        //check for errors
        if (isset($response['error']) || !$response) {
            \Log::alert(__METHOD__ . __LINE__ . 'Response logged below');

            if (!$response) {
                \Log::error('ATHENA API: Authentication failed.');
            } else {
                \Log::error(\GuzzleHttp\json_encode($response));
            }

//            Athena sends an ampty response if a patient doesn't have custom fields.
//            Therefore, this is commented out.
//            abort(400, json_encode($response));
        }

        return $response;
    }

    /**
     * Get a patient's CCDA record
     *
     * @param $patientId
     * @param $practiceId
     * @param int $departmentId
     *
     * @return mixed
     */
    public function getCcd(
        $patientId,
        $practiceId,
        $departmentId
    ) {
        $response = $this->api->GET("patients/{$patientId}/ccda", [
            'patientid'    => $patientId,
            'practiceid'   => $practiceId,
            'departmentid' => $departmentId,
            'purpose'      => 'internal',
            'xmloutput'    => false,
        ]);

        return $this->response($response);
    }

    /**
     * Get all department ids for a practice.
     *
     * @param $practiceId
     *
     * @return mixed
     */
    public function getDepartmentIds($practiceId)
    {
        $this->api->setPracticeId($practiceId);

        $response = $this->api->GET("departments", [
            'practiceid' => $practiceId,
        ]);

        return $this->response($response);
    }

    /**
     * Get the next paginated result set
     *
     * @param $url
     *
     * @return bool|mixed
     */
    public function getNextPage($url)
    {
        //@todo: this is a workaround to compensate for a bug in athena
        //it always returns production urls
        if (app()->environment('local')) {
            //removes the api version
            if (($pos = strpos($url, '/', 1)) !== false) {
                $url = substr($url, $pos + 1);
            }
        }

        //just so it doesn't append clh_practice_id to the url
        $this->api->setPracticeId(null);

        return $this->api->GET($url);
    }

    /**
     * Get the patient's custom fields
     *
     * @param $patientId
     * @param $practiceId
     * @param int $departmentId
     *
     * @return mixed
     */
    public function getPatientCustomFields(
        $patientId,
        $practiceId,
        $departmentId
    ) {
        $response = $this->api->GET("patients/{$patientId}/customfields", [
            'patientid'    => $patientId,
            'practiceid'   => $practiceId,
            'departmentid' => $departmentId,
        ]);

        return $this->response($response);
    }

    /**
     * Get the practice's custom fields
     *
     * @param $practiceId
     *
     * @return mixed
     */
    public function getPracticeCustomFields($practiceId)
    {
        //just so it doesn't append clh_practice_id to the url
        $this->api->setPracticeId($practiceId);

        $response = $this->api->GET("customfields", [
            'practiceid' => $practiceId,
        ]);

        return $this->response($response);
    }

    /**
     * Post a file (eg. pdf careplan)
     *
     * @param $patientId
     * @param $practiceId
     * @param $attachmentContent
     * @param $departmentId
     * @param string $documentSubClass
     * @param string $contentType
     *
     * @return mixed
     */
    public function postPatientDocument(
        $patientId,
        $practiceId,
        $attachmentContent,
        $departmentId,
        $documentSubClass = 'CLINICALDOCUMENT',
        $contentType = 'multipart/form-data'
    ) {
//        $response = $this->api->POST("patients/{$patientId}/documents", [
//            'patientid' => $patientId,
//            'practiceid' => $practiceId,
//            'departmentid' => $departmentId,
//            'attachmentcontents' => $attachmentContent,
//            'Content-Type' => $contentType,
//            'documentsubclass' => $documentSubClass,
//            'autoclose' => false,
//        ], [
//            'Content-type' => 'multipart/form-data',
//        ]);

        /*
         * HACK
         * @todo: Figure out why the above doesn't work
         */
        $command = "curl -v -k 'https://api.athenahealth.com/preview1/$practiceId/patients/$patientId/documents' -XPOST -F documentsubclass=$documentSubClass -F departmentid=$departmentId -F 'attachmentcontents=@$attachmentContent' -H 'Authorization: Bearer {$this->api->get_token()}'";

        $response = exec($command);

        return $this->response($response);
    }
}