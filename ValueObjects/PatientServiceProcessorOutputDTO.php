<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\ValueObjects;


use Carbon\Carbon;

class PatientServiceProcessorOutputDTO
{
    protected Carbon $chargeableMonth;
    protected int $chargeableServiceId;
    protected int $patientUserId;
    protected bool $sendToDatabase = false;
    protected bool $isFulfilling = false;

    /**
     * @return bool
     */
    public function shouldSendToDatabase(): bool
    {
        return $this->sendToDatabase;
    }

    /**
     * @param bool $sendToDatabase
     */
    public function setSendToDatabase(bool $sendToDatabase): void
    {
        $this->sendToDatabase = $sendToDatabase;
    }

    /**
     * @return Carbon
     */
    public function getChargeableMonth(): Carbon
    {
        return $this->chargeableMonth;
    }

    /**
     * @param Carbon $chargeableMonth
     */
    public function setChargeableMonth(Carbon $chargeableMonth): void
    {
        $this->chargeableMonth = $chargeableMonth;
    }

    /**
     * @return int
     */
    public function getChargeableServiceId(): int
    {
        return $this->chargeableServiceId;
    }

    /**
     * @param int $chargeableServiceId
     */
    public function setChargeableServiceId(int $chargeableServiceId): void
    {
        $this->chargeableServiceId = $chargeableServiceId;
    }

    /**
     * @return int
     */
    public function getPatientUserId(): int
    {
        return $this->patientUserId;
    }

    /**
     * @param int $patientUserId
     */
    public function setPatientUserId(int $patientUserId): void
    {
        $this->patientUserId = $patientUserId;
    }

    /**
     * @return bool
     */
    public function isFulfilling(): bool
    {
        return $this->isFulfilling;
    }

    /**
     * @param bool $isFulfilling
     */
    public function setIsFulfilling(bool $isFulfilling): void
    {
        $this->isFulfilling = $isFulfilling;
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