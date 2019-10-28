<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Contracts;

use App\Services\AthenaAPI\Connection;
use App\ValueObjects\Athena\Patient;
use App\ValueObjects\Athena\Problem;

interface AthenaApiImplementation
{
    /**
     * List or add patient problems. - POST /v1/{practiceid}/chart/{patientid}/problems.
     *
     * @see: https://developer.athenahealth.com/docs/read/chart/Problems#section-0
     */
    public function addProblem(Problem $problem);

    /**
     * @return Connection
     */
    public function api();

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
    );

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
    );

    public function createNewPatient(Patient $patient);

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
    public function getAppointmentNotes($practiceId, $appointmentId, $showDeleted = false);

    /**
     * Get available practices. Passing in practiceId of 1 will return all practices we have access to.
     *
     * @param $practiceId
     *
     * @return mixed
     */
    public function getAvailablePractices($practiceId = 1);

    public function getBillingProviderName($practiceId, $providerId);

    /**
     * Get a practise's book appointments for a date range
     * Dates are expected in mm/dd/yyyy format.
     *
     * @param $practiceId
     * @param $startDate
     * @param $endDate
     * @param bool  $showInsurance
     * @param int   $limit
     * @param int   $departmentId
     * @param mixed $offset
     * @param mixed $showCancelled
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
    );

    /**
     * Get a patient's CCDA record.
     *
     * @param $patientId
     * @param $practiceId
     * @param int $departmentId
     *
     * @return mixed
     */
    public function getCcd($patientId, $practiceId, $departmentId);

    /**
     * Get first and last name, and phone number for a patient.
     *
     * @param $patientId
     * @param $practiceId
     *
     * @return mixed
     */
    public function getDemographics($patientId, $practiceId);

    /**
     * Get all department ids for a practice.
     *
     * @param $practiceId
     * @param bool $showAllDepartments
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function getDepartmentIds($practiceId, $showAllDepartments = true);

    public function getDepartmentInfo($practiceId, $departmentId, $providerList = false);

    /**
     * Get the next paginated result set.
     *
     * @param $url
     *
     * @return bool|mixed
     */
    public function getNextPage($url);

    /**
     * Gets Information about a single patient's appointments
     * set $showPast to false to get future appointments only.
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
    public function getPatientAppointments($practiceId, $patientId, $showPast = true, $showCancelled = false);

    /**
     * Get the patient's custom fields.
     *
     * @param $patientId
     * @param $practiceId
     * @param int $departmentId
     *
     * @return mixed
     */
    public function getPatientCustomFields($patientId, $practiceId, $departmentId);

    /**
     * Get insurances for a patient.
     *
     * @param $patientId
     * @param $practiceId
     * @param $departmentId
     *
     * @throws \Exception
     */
    public function getPatientInsurances($patientId, $practiceId, $departmentId);

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
    );

    /**
     * Get problems for a patient.
     *
     * @param $patientId
     * @param $practiceId
     * @param $departmentId
     * @param bool $showDiagnosisInfo
     *
     * @return mixed
     */
    public function getPatientProblems($patientId, $practiceId, $departmentId, $showDiagnosisInfo = true);

    /**
     * Get the practice's custom fields.
     *
     * @param $practiceId
     *
     * @return mixed
     */
    public function getPracticeCustomFields($practiceId);

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
    public function postAppointmentNotes($practiceId, $appointmentId, $noteText, $showOnDisplay = false);

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
    );
}
