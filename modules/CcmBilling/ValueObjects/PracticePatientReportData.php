<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\ValueObjects;

class PracticePatientReportData
{
    public string $allBhiCodes;

    public string $allCcmProblemCodes;

    /**
     * todo.
     */
    public string $awvDate;

    public string $bhiCodes;

    public float $bhiTime;

    public string $billingCodes;

    public string $ccmProblemCodes;

    public float $ccmTime;

    public string $dob;

    public string $locationName;

    public string $name;

    public string $practice;

    public string $provider;

    public function toCsvRow(): array
    {
        return [
            'Provider Name'        => $this->provider,
            'Location'             => $this->locationName,
            'Patient Name'         => $this->name,
            'DOB'                  => $this->dob,
            'Billing Code(s)'      => $this->billingCodes,
            'CCM Mins'             => $this->ccmTime,
            'BHI Mins'             => $this->bhiTime,
            'CCM Issue(s) Treated' => $this->ccmProblemCodes,
            'All CCM Conditions'   => $this->allCcmProblemCodes,
            'BHI Issue(s) Treated' => $this->bhiCodes,
            'All BHI Conditions'   => $this->allBhiCodes,
        ];
    }
}
