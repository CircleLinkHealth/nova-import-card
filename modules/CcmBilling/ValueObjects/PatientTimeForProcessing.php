<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\ValueObjects;


use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlyTime;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class PatientTimeForProcessing
{
    private ?int $chargeableServiceId;

    private Carbon $chargeableMonth;

    private ?string $code;

    /**
     * @return string
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(?string $code): self
    {
        $this->code = $code;
        return $this;
    }


    public static function fromCollection(EloquentCollection $monthlyTimes): array
    {
        return $monthlyTimes->map(function (ChargeablePatientMonthlyTime $monthlyTimeEntity) {
            return (new self())->setChargeableServiceId($monthlyTimeEntity->chargeable_service_id)
                ->setCode(optional($monthlyTimeEntity->chargeableService)->code)
                               ->setChargeableMonth($monthlyTimeEntity->chargeable_month)
                ->setTime($monthlyTimeEntity->total_time);
        })
                         ->filter()
                         ->toArray();
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
    public function setChargeableMonth(Carbon $chargeableMonth): self
    {
        $this->chargeableMonth = $chargeableMonth;
        return $this;
    }

    /**
     * @return int
     */
    public function getChargeableServiceId(): ?int
    {
        return $this->chargeableServiceId;
    }

    /**
     * @param int $chargeableServiceId
     */
    public function setChargeableServiceId(?int $chargeableServiceId): self
    {
        $this->chargeableServiceId = $chargeableServiceId;
        return $this;
    }

    /**
     * @return int
     */
    public function getTime(): int
    {
        return $this->time;
    }

    /**
     * @param int $time
     */
    public function setTime(int $time): self
    {
        $this->time = $time;
        return $this;
    }
    private int $time;
}