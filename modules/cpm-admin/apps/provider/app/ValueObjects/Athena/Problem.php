<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\ValueObjects\Athena;

use Carbon\Carbon;

/**
 * This class describes a Problem in AthenaAPI.
 *
 * @see: https://developer.athenahealth.com/docs/read/chart/Problems#section-0
 *
 * Class Problem
 */
class Problem
{
    /**
     * The athenaNet department id. (required).
     *
     * @var int
     */
    protected $departmentId;

    /**
     * Update the laterality of this problem. Can be null, LEFT, RIGHT, or BILATERAL.
     *
     * @var string|null
     */
    protected $laterality;

    /**
     * The note to be attached to this problem.
     *
     * @var string|null
     */
    protected $note;

    /**
     * The athenaNet patient id. (required).
     *
     * @var int
     */
    protected $patientId;
    /**
     * The athenaNet practice id. (required).
     *
     * @var int
     */
    protected $practiceId;

    /**
     * The SNOMED code of the problem you are adding. (required).
     *
     * @var int
     */
    protected $snomedCode;

    /**
     * The onset date to be updated for this problem in MM/DD/YYYY format.
     *
     * @var Carbon
     */
    protected $startDate;

    /**
     * Whether the problem is chronic or acute.
     *
     * @var string
     */
    protected $status;

    /**
     * @return int
     */
    public function getDepartmentId()
    {
        return $this->departmentId;
    }

    public function getLaterality(): string
    {
        return $this->laterality;
    }

    /**
     * @return string|null
     */
    public function getNote()
    {
        return $this->note;
    }

    public function getPatientId(): int
    {
        return $this->patientId;
    }

    public function getPracticeId(): int
    {
        return $this->practiceId;
    }

    public function getSnomedCode(): int
    {
        return $this->snomedCode;
    }

    public function getStartDate(): Carbon
    {
        return $this->startDate;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param $departmentId
     */
    public function setDepartmentId($departmentId)
    {
        $this->departmentId = $departmentId;
    }

    public function setLaterality(string $laterality)
    {
        $this->laterality = $laterality;
    }

    /**
     * @param string|null $note
     */
    public function setNote($note)
    {
        $this->note = $note;
    }

    public function setPatientId(int $patientId)
    {
        $this->patientId = $patientId;
    }

    public function setPracticeId(int $practiceId)
    {
        $this->practiceId = $practiceId;
    }

    public function setSnomedCode(int $snomedCode)
    {
        $this->snomedCode = $snomedCode;
    }

    public function setStartDate(Carbon $startDate)
    {
        $this->startDate = $startDate;
    }

    public function setStatus(string $status)
    {
        $this->status = $status;
    }
}
