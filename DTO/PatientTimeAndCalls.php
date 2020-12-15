<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\DTO;

class PatientTimeAndCalls
{
    protected int $bhiTotalTime        = 0;
    protected int $ccmTotalTime        = 0;
    protected int $noOfCalls           = 0;
    protected int $noOfSuccessfulCalls = 0;
    protected int $patientId;
    protected int $pcmTotalTime = 0;
    protected int $rhcTotalTime = 0;
    protected int $rpmTotalTime = 0;

    public function fromArray(array $array): self
    {
        return $this->setCcmTotalTime($array['ccm_total_time'] ?? 0)
            ->setBhiTotalTime($array['bhi_total_time'] ?? 0)
            ->setPcmTotalTime($array['pcm_total_time'] ?? 0)
            ->setRpmTotalTime($array['rpm_total_time'] ?? 0)
            ->setRhcTotalTime($array['rhc_total_time'] ?? 0)
            ->setNoOfCalls($array['no_of_calls'] ?? 0)
            ->setNoOfSuccessfulCalls($array['no_of_successful_calls'] ?? 0);
    }

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

    public function getRhcTotalTime(): int
    {
        return $this->rhcTotalTime;
    }

    public function getRpmTotalTime(): int
    {
        return $this->rpmTotalTime;
    }

    public function setBhiTotalTime(int $bhiTotalTime): self
    {
        $this->bhiTotalTime = $bhiTotalTime;

        return $this;
    }

    public function setCcmTotalTime(int $ccmTotalTime): self
    {
        $this->ccmTotalTime = $ccmTotalTime;

        return $this;
    }

    public function setNoOfCalls(int $noOfCalls): self
    {
        $this->noOfCalls = $noOfCalls;

        return $this;
    }

    public function setNoOfSuccessfulCalls(int $noOfSuccessfulCalls): self
    {
        $this->noOfSuccessfulCalls = $noOfSuccessfulCalls;

        return $this;
    }

    public function setPatientId(int $patientId): self
    {
        $this->patientId = $patientId;

        return $this;
    }

    public function setPcmTotalTime(int $pcmTotalTime): self
    {
        $this->pcmTotalTime = $pcmTotalTime;

        return $this;
    }

    public function setRhcTotalTime(int $rhcTotalTime): self
    {
        $this->rhcTotalTime = $rhcTotalTime;

        return $this;
    }

    public function setRpmTotalTime(int $rpmTotalTime): self
    {
        $this->rpmTotalTime = $rpmTotalTime;

        return $this;
    }
}
