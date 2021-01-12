<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\ValueObjects;

class AttestationRequirementsDTO
{
    protected int $attestedBhiProblemsCount = 0;

    protected int $attestedCcmProblemsCount = 0;
    protected bool $disabled                = true;
    protected bool $hasCcm                  = false;
    protected bool $hasPcm                  = false;
    protected bool $hasRpm                  = false;

    public function getAttestedBhiProblemsCount(): int
    {
        return $this->attestedBhiProblemsCount;
    }

    public function getAttestedCcmProblemsCount(): int
    {
        return $this->attestedCcmProblemsCount;
    }

    public function hasCcm(): bool
    {
        return $this->hasCcm;
    }

    public function hasPcm(): bool
    {
        return $this->hasPcm;
    }

    public function hasRpm(): bool
    {
        return $this->hasRpm;
    }

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    public function setAttestedBhiProblemsCount(int $attestedBhiProblemsCount): void
    {
        $this->attestedBhiProblemsCount = $attestedBhiProblemsCount;
    }

    public function setAttestedCcmProblemsCount(int $attestedCcmProblemsCount): void
    {
        $this->attestedCcmProblemsCount = $attestedCcmProblemsCount;
    }

    public function setDisabled(bool $disabled): void
    {
        $this->disabled = $disabled;
    }

    public function setHasCcm(bool $hasCcm): void
    {
        $this->hasCcm = $hasCcm;
    }

    public function setHasPcm(bool $hasPcm): void
    {
        $this->hasPcm = $hasPcm;
    }

    public function setHasRpm(bool $hasRpm): void
    {
        $this->hasRpm = $hasRpm;
    }

    public function toArray(): array
    {
        return [
            'disabled'              => $this->isDisabled(),
            'has_ccm'               => $this->hasCcm(),
            'has_pcm'               => $this->hasPcm(),
            'has_rpm'               => $this->hasRpm(),
            'ccm_problems_attested' => $this->getAttestedCcmProblemsCount(),
            'bhi_problems_attested' => $this->getAttestedBhiProblemsCount(),
        ];
    }
}
