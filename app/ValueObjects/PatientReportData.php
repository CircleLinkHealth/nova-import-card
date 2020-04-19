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
    protected $allBhiCodes;

    protected $allCcmProblemCodes;
    protected $awvDate;

    protected $bhiCodes;

    protected $bhiTime;

    protected $billingCodes;

    protected $ccmProblemCodes;

    protected $ccmTime;

    protected $dob;

    protected $enableAllProblemCodesColumnns;

    protected $locationName;

    protected $name;

    protected $practice;

    protected $provider;

    /**
     * @return mixed
     */
    public function getAllBhiCodes()
    {
        return $this->allBhiCodes;
    }

    /**
     * @return mixed
     */
    public function getAllCcmProblemCodes()
    {
        return $this->allCcmProblemCodes;
    }

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
    public function getBhiCodes()
    {
        return $this->bhiCode;
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
    public function getEnableAllProblemCodesColumnns()
    {
        return $this->enableAllProblemCodesColumnns;
    }

    /**
     * @return mixed
     */
    public function getLocationName()
    {
        return $this->locationName;
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
     * @param mixed $allBhiCodes
     */
    public function setAllBhiCodes($allBhiCodes): void
    {
        $this->allBhiCodes = $allBhiCodes;
    }

    /**
     * @param mixed $allCcmProblemCodes
     */
    public function setAllCcmProblemCodes($allCcmProblemCodes): void
    {
        $this->allCcmProblemCodes = $allCcmProblemCodes;
    }

    /**
     * @param mixed $awvDate
     */
    public function setAwvDate($awvDate): void
    {
        $this->awvDate = $awvDate;
    }

    /**
     * @param mixed $bhiCodes
     */
    public function setBhiCodes($bhiCodes): void
    {
        $this->bhiCode = $bhiCodes;
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
     * @param mixed $enableAllProblemCodesColumnns
     */
    public function setEnableAllProblemCodesColumnns($enableAllProblemCodesColumnns): void
    {
        $this->enableAllProblemCodesColumnns = $enableAllProblemCodesColumnns;
    }

    /**
     * @param mixed $locationName
     */
    public function setLocationName($locationName): void
    {
        $this->locationName = $locationName;
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
