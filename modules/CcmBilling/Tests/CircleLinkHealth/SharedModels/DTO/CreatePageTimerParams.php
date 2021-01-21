<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\DTO;

class CreatePageTimerParams
{
    public array $activity           = [];
    public ?string $ipAddr           = null;
    public ?string $patientId        = null;
    public ?string $programId        = null;
    public ?string $providerId       = null;
    public ?string $redirectLocation = null;
    public ?string $userAgent        = null;

    public function getActivity(): array
    {
        return $this->activity;
    }

    public function getIpAddr(): ?string
    {
        return $this->ipAddr;
    }

    public function getPatientId(): ?string
    {
        return $this->patientId;
    }

    public function getProgramId(): ?string
    {
        return $this->programId;
    }

    public function getProviderId(): ?string
    {
        return $this->providerId;
    }

    public function getRedirectLocation(): ?string
    {
        return $this->redirectLocation;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setActivity(array $activity): CreatePageTimerParams
    {
        $this->activity = $activity;

        return $this;
    }

    public function setIpAddr(?string $ipAddr): CreatePageTimerParams
    {
        $this->ipAddr = $ipAddr;

        return $this;
    }

    public function setPatientId(?string $patientId): CreatePageTimerParams
    {
        $this->patientId = $patientId;

        return $this;
    }

    public function setProgramId(?string $programId): CreatePageTimerParams
    {
        $this->programId = $programId;

        return $this;
    }

    public function setProviderId(?string $providerId): CreatePageTimerParams
    {
        $this->providerId = $providerId;

        return $this;
    }

    public function setRedirectLocation(?string $redirectLocation): CreatePageTimerParams
    {
        $this->redirectLocation = $redirectLocation;

        return $this;
    }

    public function setUserAgent(?string $userAgent): CreatePageTimerParams
    {
        $this->userAgent = $userAgent;

        return $this;
    }
}
