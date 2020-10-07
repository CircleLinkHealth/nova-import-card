<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\AthenaAPI;

use App\Traits\ValidatesDates;
use App\ValueObjects\Athena\Patient;
use App\ValueObjects\Athena\Problem;
use CircleLinkHealth\Eligibility\Contracts\AthenaApiConnection;
use CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation;

class Calls implements AthenaApiImplementation
{
    use ValidatesDates;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * List or add patient problems. - POST /v1/{practiceid}/chart/{patientid}/problems.
     *
     * @see: https://developer.athenahealth.com/docs/read/chart/Problems#section-0
     */
    public function addProblem(Problem $problem)
    {
        $practiceId = $problem->getPracticeId();

        if ( ! $practiceId) {
            throw new \Exception('practiceid is required.', 422);
        }

        $patientId = $problem->getPatientId();

        if ( ! $patientId) {
            throw new \Exception('practiceid is required.', 422);
        }

        $response = $this->api()->POST(
            "{$practiceId}/chart/{$patientId}/problems",
            [
                'departmentid' => $problem->getDepartmentId(),
                'snomedcode'   => $problem->getSnomedCode(),
                'status'       => $problem->getStatus(),
            ]
        );

        return $this->response($response);
    }

    /**
     * @return Connection
     */
    public function api()
    {
        if ( ! $this->connection instanceof Connection) {
            $this->connection = app(AthenaApiConnection::class);
        }

        if ( ! $this->connection instanceof Connection) {
            throw new \Exception('AthenaAPI Connection not initialized');
        }

        return $this->connection;
    }

    /**
     * @param $practiceId
     * @param $departmentId
     * @param $patientId
     * @param $providerId
     * @param $appointmentId
     * @param null $reasonId
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function createNewAppointment(
        $practiceId,
        $departmentId,
        $patientId,
        $providerId,
        $appointmentId,
        $reasonId
    ) {
        $this->api()->setPracticeId($practiceId);

        $response = $this->api()->PUT(
            "appointments/${appointmentId}",
            [
                'practiceid'                  => $practiceId,
                'departmentid'                => $departmentId,
                'patientid'                   => $patientId,
                'providerid'                  => $providerId,
                'appointmentid'               => $appointmentId,
                'reasonid'                    => $reasonId,
                'Content-Type'                => 'application/x-www-form-urlencoded',
                'ignoreschedulablepermission' => false,
            ]
        );

        return $this->response($response);
    }

    /**
     * Creates new apointment slot for testing
     * Returns appointment id and time of appointment slot.
     * We use that id to create new appointment in the slot.
     *
     * @param $practiceId
     * @param $providerId
     * @param $departmentId
     * @param $reasonId
     * @param $appointmentDate
     * @param $appointmentTime
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function createNewAppointmentSlot(
        $practiceId,
        $departmentId,
        $providerId,
        $reasonId,
        $appointmentDate,
        $appointmentTime
    ) {
        $this->api()->setPracticeId($practiceId);

        $response = $this->api()->POST(
            'appointments/open',
            [
                'practiceid'      => $practiceId,
                'departmentid'    => $departmentId,
                'providerid'      => $providerId,
                'reasonid'        => $reasonId,
                'appointmentdate' => $appointmentDate,
                'appointmenttime' => $appointmentTime,
            ]
        );

        return $this->response($response);
    }

    //create method to create patient in athena (for testing), issue with date format
    public function createNewPatient(Patient $patient)
    {
        $practiceId = $patient->getPracticeId();

        if ( ! $practiceId) {
            throw new \Exception('practiceid is required.', 422);
        }

        $response = $this->api()->POST(
            "{$practiceId}/patients",
            [
                'departmentid' => $patient->getDepartmentId(),
                'dob'          => $patient->getDob(),
                'firstname'    => $patient->getFirstName(),
                'lastname'     => $patient->getLastName(),
                'address1'     => $patient->getAddress1(),
                'address2'     => $patient->getAddress2(),
                'donotcallyn'  => $patient->getDoNotCall(),
                'city'         => $patient->getCity(),
                'email'        => $patient->getEmail(),
                'homephone'    => $patient->getHomePhone(),
                'mobilephone'  => $patient->getMobilePhone(),
                'state'        => $patient->getState(),
                'zip'          => $patient->getZip(),
                'sex'          => $patient->getGender(),
            ]
        );

        //returns patient Id
        return $this->response($response);
    }

    /**
     * Retrieve notes for an appointment.
     *
     * @param $practiceId
     * @param $appointmentId
     * @param bool $showDeleted
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function getAppointmentNotes(
        $practiceId,
        $appointmentId,
        $showDeleted = false
    ) {
        $this->api()->setPracticeId($practiceId);

        $response = $this->api()->GET(
            "appointments/{$appointmentId}/notes",
            [
                'showdeleted' => $showDeleted,
            ]
        );

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
        $response = $this->api()->GET(
            "${practiceId}/practiceinfo",
            [
                //$practiceId defaults to 1, which will give us all practices we have access to
            ]
        );

        return $this->response($response);
    }

    public function getBillingProviderName($practiceId, $providerId)
    {
        $this->api()->setPracticeId($practiceId);

        $response = $this->api()->GET(
            "providers/${providerId}",
            [
                'showallproviderids' => true,
            ]
        );

        return $this->response($response);
    }

    /**
     * Get a practise's book appointments for a date range
     * Dates are expected in mm/dd/yyyy format.
     *
     * @param $practiceId
     * @param $startDate
     * @param $endDate
     * @param int   $departmentId
     * @param mixed $offset
     * @param bool  $showInsurance
     * @param int   $limit
     * @param mixed $showCancelled
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function getBookedAppointments(
        $practiceId,
        $startDate,
        $endDate,
        $departmentId,
        $offset = 0,
        $showInsurance = false,
        $limit = 1000,
        $showCancelled = false
    ) {
        $this->api()->setPracticeId($practiceId);

        $response = $this->api()->GET(
            'appointments/booked',
            [
                'practiceid'    => $practiceId,
                'startdate'     => $startDate,
                'enddate'       => $endDate,
                'departmentid'  => $departmentId,
                'showinsurance' => $showInsurance,
                'limit'         => $limit,
                'showcancelled' => $showCancelled,
                'offset'        => $offset,
            ]
        );

        return $this->response($response);
    }

    /**
     * Get care team associated with the patient and chart.
     *
     * @see https://developer.athenahealth.com/docs/read/chart/Care_Team#section-1
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function getCareTeam(int $patientId, int $practiceId, int $departmentId)
    {
        $this->api()->setPracticeId($practiceId);

        $response = $this->api()->GET(
            "chart/{$patientId}/careteam",
            [
                'patientid'    => $patientId,
                'practiceid'   => $practiceId,
                'departmentid' => $departmentId,
            ]
        );

        return $this->response($response);
    }

    /**
     * Get a patient's CCDA record.
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
        $this->api()->setPracticeId($practiceId);

        $response = $this->api()->GET(
            "patients/{$patientId}/ccda",
            [
                'patientid'    => $patientId,
                'practiceid'   => $practiceId,
                'departmentid' => $departmentId,
                'purpose'      => 'internal',
                'xmloutput'    => false,
            ]
        );

        return $this->response($response);
    }

    /**
     * Get first and last name, and phone number for a patient.
     *
     * @param $patientId
     * @param $practiceId
     *
     * @return mixed
     */
    public function getDemographics($patientId, $practiceId)
    {
        $this->api()->setPracticeId($practiceId);

        $response = $this->api()->GET("patients/{$patientId}");

        return $this->response($response);
    }

    public function getDepartmentInfo($practiceId, $departmentId, $providerList = false)
    {
        $this->api()->setPracticeId($practiceId);

        $response = $this->api()->GET(
            "departments/${departmentId}",
            [
                'providerlist' => $providerList,
            ]
        );

        return $this->response($response);
    }

    /**
     * Get all department ids for a practice.
     *
     * @param $practiceId
     * @param bool $showAllDepartments
     * @param bool $providerlist
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function getDepartments($practiceId, $showAllDepartments = false, $providerlist = false)
    {
        $this->api()->setPracticeId($practiceId);

        $response = $this->api()->GET(
            'departments',
            [
                'practiceid'         => $practiceId,
                'showalldepartments' => $showAllDepartments,
                'providerlist'       => $providerlist,
            ]
        );

        return $this->response($response);
    }

    /**
     * Get a patient's medical history.
     *
     * @param string $startDate
     * @param string $endDate
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function getEncounters(int $patientId, int $practiceId, int $departmentId, string $startDate = null, string $endDate = null)
    {
        $args = [
            'departmentid' => $departmentId,
            'patientid'    => $patientId,
            'practiceid'   => $practiceId,
        ];

        if ($this->isValidDate($startDate)) {
            $args['startdate'] = $startDate;
        }

        if ($this->isValidDate($endDate)) {
            $args['enddate'] = $endDate;
        }

        $response = $this->api()->GET(
            "${practiceId}/chart/${patientId}/encounters",
            $args
        );

        return $this->response($response);
    }

    /**
     * Get a patient's medical history.
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function getMedicalHistory(int $patientId, int $practiceId, int $departmentId)
    {
        $this->api()->setPracticeId($practiceId);
    
        $response = $this->api()->GET(
            "chart/${patientId}/medicalhistory",
            [
                'departmentid' => $departmentId,
                'patientid'    => $patientId,
                'practiceid'   => $practiceId,
            ]
        );

        return $this->response($response);
    }

    /**
     * Get patient medications.
     *
     * @throws \Exception
     *
     * @return array|mixed
     */
    public function getMedications(int $patientId, int $practiceId, int $departmentId)
    {
        $response = $this->api()->GET(
            "${practiceId}/chart/${patientId}/medications",
            [
                'departmentid' => $departmentId,
                'patientid'    => $patientId,
                'practiceid'   => $practiceId,
            ]
        );

        return $this->response($response);
    }

    /**
     * Get the next paginated result set.
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
            if (false !== ($pos = strpos($url, '/', 1))) {
                $url = substr($url, $pos + 1);
            }
        }

        //just so it doesn't append clh_practice_id to the url
        $this->api()->setPracticeId(null);

        return $this->api()->GET($url);
    }

    /**
     * Gets Information about a single patient's appointments
     * set $showPast to false to get future appointments only.
     *
     * Sample:
     * array (
     * 0 =>
     * array (
     * 'date' => '07/17/2020',
     * 'copay' => 0,
     * 'duration' => '10',
     * 'appointmenttypeid' => '1',
     * 'appointmentid' => '1234',
     * 'appointmenttype' => 'MD FOLLOW UP',
     * 'starttime' => '09:00',
     * 'patientappointmenttypename' => 'Follow-Up',
     * 'departmentid' => '1',
     * 'providerid' => '1',
     * 'appointmentstatus' => 'f',
     * ),
     * )
     *
     * @param $practiceId
     * @param $patientId
     * @param bool $showPast
     * @param bool $showCancelled
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function getPatientAppointments(
        ?int $practiceId,
        ?int $patientId,
        $showPast = true,
        $showCancelled = false
    ) {
        $this->api()->setPracticeId($practiceId);

        $response = $this->api()->GET(
            "patients/{$patientId}/appointments",
            [
                'showpast'      => $showPast,
                'showcancelled' => $showCancelled,
            ]
        );

        return $this->response($response);
    }

    /**
     * Get the patient's custom fields.
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
        $response = $this->api()->GET(
            "patients/{$patientId}/customfields",
            [
                'patientid'    => $patientId,
                'practiceid'   => $practiceId,
                'departmentid' => $departmentId,
            ]
        );

        return $this->response($response);
    }

    /**
     * Get insurances for a patient.
     *
     * @param $patientId
     * @param $practiceId
     * @param $departmentId
     *
     * @throws \Exception
     */
    public function getPatientInsurances($patientId, $practiceId, $departmentId)
    {
        $this->api()->setPracticeId($practiceId);

        $apiPath = "patients/${patientId}/insurances";

        $response = $this->api()->GET(
            $apiPath,
            [
                'departmentid'  => $departmentId,
                'showfullssn'   => false,
                'showcancelled' => false,
            ]
        );

        return $this->response(
            array_merge(
                $response,
                [
                    'api_path' => $apiPath,
                ]
            )
        );
    }

    /**
     * Get primary provider for a patient (if set).
     *
     *From Athena docs: 'Find a patient. At least one of the following is required:
     * guarantorfirstname, firstname, dob, workphone, departmentid, guarantorsuffix,
     * guarantorlastname, mobilephone, middlename, suffix, guarantormiddlename, homephone, lastname.'
     *
     * @param $practiceId
     * @param null $patientFirstName
     * @param null $patientMiddleName
     * @param null $patientLastName
     * @param null $dob
     * @param null $mobilephone
     * @param null $homephone
     * @param null $workphone
     * @param null $departmentId
     *
     * @throws \Exception
     *
     * @return mixed
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
        $response = $this->api()->GET(
            "${practiceId}/patients",
            [
                'firstname'    => $patientFirstName,
                'middlename'   => $patientMiddleName,
                'lastname'     => $patientLastName,
                'dob'          => $dob,
                'mobilephone'  => $mobilephone,
                'homephone'    => $homephone,
                'workphone'    => $workphone,
                'departmentid' => $departmentId,
            ]
        );

        return $this->response($response);
    }

    /**
     * Get problems for a patient.
     *
     * @param int  $patientId
     * @param int  $practiceId
     * @param int  $departmentId
     * @param bool $showDiagnosisInfo
     * @param bool $showinactive
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function getPatientProblems(
        $patientId,
        $practiceId,
        $departmentId,
        $showDiagnosisInfo = true,
        $showinactive = false
    ) {
        $response = $this->api()->GET(
            "${practiceId}/chart/${patientId}/problems",
            [
                'departmentid'      => $departmentId,
                'showdiagnosisinfo' => $showDiagnosisInfo,
                'showinactive'      => $showinactive,
            ]
        );

        return $this->response($response);
    }

    /**
     * Get the practice's custom fields.
     *
     * @param $practiceId
     *
     * @return mixed
     */
    public function getPracticeCustomFields($practiceId)
    {
        //just so it doesn't append clh_practice_id to the url
        $this->api()->setPracticeId($practiceId);

        $response = $this->api()->GET(
            'customfields',
            [
                'practiceid' => $practiceId,
            ]
        );

        return $this->response($response);
    }

    public function getProvider(
        $practiceId,
        $providerId
    ) {
        $this->api()->setPracticeId($practiceId);

        $response = $this->api()->GET("providers/{$providerId}");

        return $this->response($response);
    }

    /**
     * Add a note for an appointment.
     * Can be desplayed on homescreen.
     *
     * @param $practiceId
     * @param $appointmentId
     * @param bool $showOnDesplay
     * @param $noteText
     * @param mixed $showOnDisplay
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function postAppointmentNotes(
        $practiceId,
        $appointmentId,
        $noteText,
        $showOnDisplay = false
    ) {
        $this->api()->setPracticeId($practiceId);

        $response = $this->api()->POST(
            "appointments/{$appointmentId}/notes",
            [
                'displayonschedule' => $showOnDisplay,
                'notetext'          => $noteText,
            ]
        );

        return $this->response($response);
    }

    /**
     * Post a file (eg. pdf careplan).
     *
     * @param $patientId
     * @param $practiceId
     * @param $attachmentContent
     * @param $departmentId
     * @param string     $documentSubClass
     * @param string     $contentType
     * @param mixed|null $appointmentId
     *
     * @return mixed
     */
    public function postPatientDocument(
        $patientId,
        $practiceId,
        $attachmentContent,
        $departmentId,
        $appointmentId = null,
        $documentSubClass = 'CLINICALDOCUMENT',
        $contentType = 'multipart/form-data'
    ) {
//        $response = $this->api()->POST("patients/{$patientId}/documents", [
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

        $version = $this->api()->getVersion();

        /*
         * HACK
         * @todo: Figure out why the above doesn't work
         */
        $appointmentField = $appointmentId
            ? "-F appointmentid=${appointmentId}"
            : '';

        $command = "curl -v -k 'https://api.athenahealth.com/${version}/${practiceId}/patients/${patientId}/documents' -XPOST -F documentsubclass=${documentSubClass} -F departmentid=${departmentId} ${appointmentField} -F 'attachmentcontents=@${attachmentContent}' -H 'Authorization: Bearer {$this->api()->get_token()}'";

        $response = exec($command);

        return $this->response($response);
    }

    /**
     * Checks if the response contains errors. If it does, it logs the response and throws an Exception.
     *
     * @param $response
     *
     * @throws \Exception
     *
     * @return mixed
     */
    private function response($response)
    {
        //check for errors
        if (is_array($response) && array_key_exists('error', $response)) {
            \Log::alert(__METHOD__.__LINE__.'Response logged below '.PHP_EOL);

            \Log::error(\GuzzleHttp\json_encode($response));

            if ( ! empty($response)) {
                abort(400, json_encode($response));
            }
        }

        return $response;
    }
}
