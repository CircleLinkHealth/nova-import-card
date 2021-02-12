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
    protected bool $isFulfilling = false;
    protected int $patientUserId;
    protected bool $sendToDatabase = false;

    public function getChargeableMonth(): Carbon
    {
        return $this->chargeableMonth;
    }

    public function getChargeableServiceId(): int
    {
        return $this->chargeableServiceId;
    }

    public function getPatientUserId(): int
    {
        return $this->patientUserId;
    }

    public function isFulfilling(): bool
    {
        return $this->isFulfilling;
    }

    public function setChargeableMonth(Carbon $chargeableMonth): void
    {
        $this->chargeableMonth = $chargeableMonth;
    }

    public function setChargeableServiceId(int $chargeableServiceId): void
    {
        $this->chargeableServiceId = $chargeableServiceId;
    }

    public function setIsFulfilling(bool $isFulfilling): void
    {
        $this->isFulfilling = $isFulfilling;
    }

    public function setPatientUserId(int $patientUserId): void
    {
        $this->patientUserId = $patientUserId;
    }

    public function setSendToDatabase(bool $sendToDatabase): void
    {
        $this->sendToDatabase = $sendToDatabase;
    }

    public function shouldSendToDatabase(): bool
    {
        return $this->sendToDatabase;
    }

    public function toArray(): array
    {
        return [
            'patient_user_id'       => $this->patientUserId,
            'chargeable_service_id' => $this->chargeableServiceId,
            'chargeable_month'      => $this->chargeableMonth,
            'is_fulfilled'          => $this->isFulfilling,
        ];
    }
}
