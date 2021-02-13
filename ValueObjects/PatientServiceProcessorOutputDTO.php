<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\ValueObjects;

use Carbon\Carbon;

class PatientServiceProcessorOutputDTO
{
    protected Carbon $chargeableMonth;
    protected int $chargeableServiceId;
    protected string $code;
    protected bool $isFulfilling = false;
    protected int $patientUserId;
    protected bool $requiresConsent = false;
    protected bool $sendToDatabase  = false;
    protected int $totalTime = 0;

    public function getChargeableMonth(): Carbon
    {
        return $this->chargeableMonth;
    }

    public function getChargeableServiceId(): int
    {
        return $this->chargeableServiceId;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getPatientUserId(): int
    {
        return $this->patientUserId;
    }

    public function isFulfilling(): bool
    {
        return $this->isFulfilling;
    }

    public function requiresConsent(): bool
    {
        return $this->requiresConsent;
    }

    public function setChargeableMonth(Carbon $chargeableMonth): self
    {
        $this->chargeableMonth = $chargeableMonth;

        return $this;
    }

    public function setChargeableServiceId(int $chargeableServiceId): self
    {
        $this->chargeableServiceId = $chargeableServiceId;

        return $this;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function setIsFulfilling(bool $isFulfilling): self
    {
        $this->isFulfilling = $isFulfilling;

        return $this;
    }

    public function setPatientUserId(int $patientUserId): self
    {
        $this->patientUserId = $patientUserId;

        return $this;
    }

    public function setRequiresConsent(bool $requiresConsent): self
    {
        $this->requiresConsent = $requiresConsent;

        return $this;
    }

    public function setSendToDatabase(bool $sendToDatabase): self
    {
        $this->sendToDatabase = $sendToDatabase;

        return $this;
    }

    public function shouldSendToDatabase(): bool
    {
        return $this->sendToDatabase;
    }

    public function toArray(): array
    {
        return [
            'patient_user_id'          => $this->patientUserId,
            'chargeable_service_id'    => $this->chargeableServiceId,
            'chargeable_month'         => $this->chargeableMonth,
            'requires_patient_consent' => $this->requiresConsent,
            'is_fulfilled'             => $this->isFulfilling,
        ];
    }

    public function toPatientChargeableServiceForProcessingDTO(): PatientChargeableServicesForProcessing
    {
        return (new PatientChargeableServicesForProcessing())
            ->setChargeableServiceId($this->getChargeableServiceId())
            ->setIsFulfilled($this->isFulfilling())
            ->setCode($this->getCode())
            ->setRequiresConsent($this->requiresConsent());
    }
}
