<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\ValueObjects;

/**
 * Class PatientReportData.
 * This class describes the patient data on the patient report created along with Practice Invoices.
 */
class PatientReportData
{
    protected $awvDate;

    protected $bhiCode;

    protected $bhiProblem;

    protected $bhiTime;

    protected $billingCodes;

    protected $ccmProblemCodes;

    protected $ccmTime;

    protected $dob;

    protected $name;

    protected $practice;

    protected $provider;

    /**
     * @return mixed
     */
    public function getAwvDate()
    {
        return $this->awvDate;
    }

    /**
     * @return mixed
     */
    public function getBhiCode()
    {
        return $this->bhiCode;
    }

    /**
     * @return mixed
     */
    public function getBhiProblem()
    {
        return $this->bhiProblem;
    }

    /**
     * @return mixed
     */
    public function getBhiTime()
    {
        return $this->bhiTime;
    }

    /**
     * @return mixed
     */
    public function getBillingCodes()
    {
        return $this->billingCodes;
    }

    /**
     * @return mixed
     */
    public function getCcmProblemCodes()
    {
        return $this->ccmProblemCodes;
    }

    /**
     * @return mixed
     */
    public function getCcmTime()
    {
        return $this->ccmTime;
    }

    /**
     * @return mixed
     */
    public function getDob()
    {
        return $this->dob;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getPractice()
    {
        return $this->practice;
    }

    /**
     * @return mixed
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param mixed $awvDate
     */
    public function setAwvDate($awvDate): void
    {
        $this->awvDate = $awvDate;
    }

    /**
     * @param mixed $bhiCode
     */
    public function setBhiCode($bhiCode): void
    {
        $this->bhiCode = $bhiCode;
    }

    /**
     * @param mixed $bhiProblem
     */
    public function setBhiProblem($bhiProblem): void
    {
        $this->bhiProblem = $bhiProblem;
    }

    /**
     * @param mixed $bhiTime
     */
    public function setBhiTime($bhiTime): void
    {
        $this->bhiTime = $bhiTime;
    }

    /**
     * @param mixed $billingCodes
     */
    public function setBillingCodes($billingCodes): void
    {
        $this->billingCodes = $billingCodes;
    }

    /**
     * @param mixed $ccmProblemCodes
     */
    public function setCcmProblemCodes($ccmProblemCodes): void
    {
        $this->ccmProblemCodes = $ccmProblemCodes;
    }

    /**
     * @param mixed $ccmTime
     */
    public function setCcmTime($ccmTime): void
    {
        $this->ccmTime = $ccmTime;
    }

    /**
     * @param mixed $dob
     */
    public function setDob($dob): void
    {
        $this->dob = $dob;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @param mixed $practice
     */
    public function setPractice($practice): void
    {
        $this->practice = $practice;
    }

    /**
     * @param mixed $problem1
     */
    public function setProblem1($problem1): void
    {
        $this->problem1 = $problem1;
    }

    /**
     * @param mixed $problem1Code
     */
    public function setProblem1Code($problem1Code): void
    {
        $this->problem1Code = $problem1Code;
    }

    /**
     * @param mixed $problem2
     */
    public function setProblem2($problem2): void
    {
        $this->problem2 = $problem2;
    }

    /**
     * @param mixed $problem2Code
     */
    public function setProblem2Code($problem2Code): void
    {
        $this->problem2Code = $problem2Code;
    }

    /**
     * @param mixed $provider
     */
    public function setProvider($provider): void
    {
        $this->provider = $provider;
    }
}
