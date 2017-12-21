<?php

namespace App\Services\AthenaAPI;

use App\ValueObjects\Athena\Patient;

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

        $this->api = new Connection($this->version, $this->key, $this->secret);
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
        if (isset($response['error'])) {
            \Log::alert(__METHOD__ . __LINE__ . 'Response logged below ' . PHP_EOL);

            \Log::error(\GuzzleHttp\json_encode($response));

            if (!empty($response)) {
                abort(400, json_encode($response));
            }
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
        $this->api->setPracticeId($practiceId);

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
     * Get problems for a patient
     *
     * @param $patientId
     * @param $practiceId
     * @param $departmentId
     * @param bool $showDiagnosisInfo
     *
     * @return mixed
     */
    public function getPatientProblems($patientId, $practiceId, $departmentId, $showDiagnosisInfo = true) {

        $response = $this->api->GET("$practiceId/chart/$patientId/problems", [
            'departmentid' => $departmentId,
            'showdiagnosisinfo' => $showDiagnosisInfo
        ]);

        return $this->response($response);
    }

    /**
     * Get insurances for a patient
     *
     * @param $patientId
     * @param $practiceId
     * @param $departmentId
     *
     *
     * @return mixed
     */
    public function getPatientInsurances($patientId, $practiceId, $departmentId) {

        $response = $this->api->GET("$practiceId/patients/$patientId/insurances", [
            'departmentid' => $departmentId,
        ]);

        return $this->response($response);
    }


    /**
     * Get first and last name, and phone number for a patient
     *
     * @param $patientId
     * @param $practiceId
     *
     * @return mixed
     */
    public function getPatientNameAndPhone($patientId, $practiceId) {

        $response = $this->api->GET("$practiceId/patients/$patientId");

        return $this->response($response);
    }



    /**
     * Get primary provider for a patient (if set)
     *
     *From Athena docs: 'Find a patient. At least one of the following is required:
     * guarantorfirstname, firstname, dob, workphone, departmentid, guarantorsuffix,
     * guarantorlastname, mobilephone, middlename, suffix, guarantormiddlename, homephone, lastname.'
     *
     * @param $practiceId
     *
     * @param null $patientFirstName
     * @param null $patientMiddleName
     * @param null $patientLastName
     * @param null $dob
     * @param null $mobilephone
     * @param null $homephone
     * @param null $workphone
     * @param null $departmentId
     * @return mixed
     * @throws \Exception
     */
    public function getPatientPrimaryProvider(
        $practiceId,
        $patientFirstName = null,
        $patientMiddleName = null,
        $patientLastName = null,
        $dob = null,
        $mobilephone = null,
        $homephone = null,
        $workphone = null,
        $departmentId = null
    ) {
        $response = $this->api->GET("$practiceId/patients", [
            'firstname' => $patientFirstName,
            'middlename' => $patientMiddleName,
            'lastname' => $patientLastName,
            'dob' => $dob,
            'mobilephone' => $mobilephone,
            'homephone' => $homephone,
            'workphone' => $workphone,
            'departmentid' => $departmentId
        ]);

        return $this->response($response);
    }





    /**
     * Get available practices. Passing in practiceId of 1 will return all practices we have access to.
     *
     * @param $practiceId
     *
     * @return mixed
     */
    public function getAvailablePractices($practiceId = 1)
    {
        $response = $this->api->GET("$practiceId/practiceinfo", [
            //$practiceId defaults to 1, which will give us all practices we have access to
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

        $version = env('ATHENA_VERSION');

        /*
         * HACK
         * @todo: Figure out why the above doesn't work
         */
        $command = "curl -v -k 'https://api.athenahealth.com/$version/$practiceId/patients/$patientId/documents' -XPOST -F documentsubclass=$documentSubClass -F departmentid=$departmentId -F 'attachmentcontents=@$attachmentContent' -H 'Authorization: Bearer {$this->api->get_token()}'";

        $response = exec($command);

        return $this->response($response);
    }


    //create method to create patient in athena (for testing), issue with date format
    public function createNewPatient(Patient $patient){

        $response = $this->api->POST("{$patient->getPracticeId()}/patients", [
            'departmentid' => $patient->getDepartmentId(),
            'dob' => $patient->getDob(),
            'firstname' => $patient->getFirstName(),
            'lastname' => $patient->getLastName(),
            'address1' => $patient->getAddress1(),
            'address2' => $patient->getAddress2(),
            'donotcallyn' => $patient->getDoNotCall(),
            'city' => $patient->getCity(),
            'email' => $patient->getEmail(),
            'homephone' => $patient->getHomePhone(),
            'mobilephone' => $patient->getMobilePhone(),
            'state' => $patient->getState(),
            'zip' => $patient->getZip(),
            'sex' => $patient->getGender(),
        ]);

        //returns patient Id
        return $this->response($response);
    }

}
