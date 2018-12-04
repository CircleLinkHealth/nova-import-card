<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 12/22/2017
 * Time: 1:44 PM
 */

namespace App\ValueObjects\Athena;

use Carbon\Carbon;

/**
 * This class describes a Problem in AthenaAPI.
 *
 * @see: https://developer.athenahealth.com/docs/read/chart/Problems#section-0
 *
 * Class Problem
 * @package App\ValueObjects\Athena
 */
class Problem
{
    /**
     * The athenaNet practice id. (required)
     *
     * @var integer
     */
    protected $practiceId;

    /**
     * The athenaNet patient id. (required)
     *
     * @var integer
     */
    protected $patientId;

    /**
     * The athenaNet department id. (required)
     *
     * @var integer
     */
    protected $departmentId;

    /**
     * Update the laterality of this problem. Can be null, LEFT, RIGHT, or BILATERAL.
     *
     * @var string|null
     */
    protected $laterality = null;

    /**
     * The note to be attached to this problem.
     *
     * @var string|null
     */
    protected $note;

    /**
     * The SNOMED code of the problem you are adding. (required)
     *
     * @var integer
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

    /**
     * @param $departmentId
     */
    public function setDepartmentId($departmentId)
    {
        $this->departmentId = $departmentId;
    }

    /**
     * @return string
     */
    public function getLaterality(): string
    {
        return $this->laterality;
    }

    /**
     * @param string $laterality
     */
    public function setLaterality(string $laterality)
    {
        $this->laterality = $laterality;
    }

    /**
     * @return null|string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param null|string $note
     */
    public function setNote($note)
    {
        $this->note = $note;
    }

    /**
     * @return int
     */
    public function getSnomedCode(): int
    {
        return $this->snomedCode;
    }

    /**
     * @param int $snomedCode
     */
    public function setSnomedCode(int $snomedCode)
    {
        $this->snomedCode = $snomedCode;
    }

    /**
     * @return Carbon
     */
    public function getStartDate(): Carbon
    {
        return $this->startDate;
    }

    /**
     * @param Carbon $startDate
     */
    public function setStartDate(Carbon $startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @return int
     */
    public function getPracticeId(): int
    {
        return $this->practiceId;
    }

    /**
     * @param int $practiceId
     */
    public function setPracticeId(int $practiceId)
    {
        $this->practiceId = $practiceId;
    }

    /**
     * @return int
     */
    public function getPatientId(): int
    {
        return $this->patientId;
    }

    /**
     * @param int $patientId
     */
    public function setPatientId(int $patientId)
    {
        $this->patientId = $patientId;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status)
    {
        $this->status = $status;
    }
}
