<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\ValueObjects;

class PatientTimeAndCalls
{
    protected int $bhiTotalTime        = 0;
    protected int $ccmTotalTime        = 0;
    protected int $noOfCalls           = 0;
    protected int $noOfSuccessfulCalls = 0;
    protected int $patientId;
    protected int $pcmTotalTime = 0;
    protected int $rpmTotalTime = 0;

    public function getBhiTotalTime(): int
    {
        return $this->bhiTotalTime;
    }

    public function getCcmTotalTime(): int
    {
        return $this->ccmTotalTime;
    }

    public function getNoOfCalls(): int
    {
        return $this->noOfCalls;
    }

    public function getNoOfSuccessfulCalls(): int
    {
        return $this->noOfSuccessfulCalls;
    }

    public function getPatientId(): int
    {
        return $this->patientId;
    }

    public function getPcmTotalTime(): int
    {
        return $this->pcmTotalTime;
    }

    public function getRpmTotalTime(): int
    {
        return $this->rpmTotalTime;
    }

    public function setBhiTotalTime(int $bhiTotalTime): void
    {
        $this->bhiTotalTime = $bhiTotalTime;
    }

    public function setCcmTotalTime(int $ccmTotalTime): void
    {
        $this->ccmTotalTime = $ccmTotalTime;
    }

    public function setNoOfCalls(int $noOfCalls): void
    {
        $this->noOfCalls = $noOfCalls;
    }

    public function setNoOfSuccessfulCalls(int $noOfSuccessfulCalls): void
    {
        $this->noOfSuccessfulCalls = $noOfSuccessfulCalls;
    }

    public function setPatientId(int $patientId): void
    {
        $this->patientId = $patientId;
    }

    public function setPcmTotalTime(int $pcmTotalTime): void
    {
        $this->pcmTotalTime = $pcmTotalTime;
    }

    public function setRpmTotalTime(int $rpmTotalTime): void
    {
        $this->rpmTotalTime = $rpmTotalTime;
    }
}
