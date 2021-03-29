<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\ValueObjects;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class PatientTimeForProcessing
{
    private Carbon $chargeableMonth;
    private ?int $chargeableServiceId;

    private ?string $code;
    private int $time;

    public static function fromCollection(EloquentCollection $monthlyTimes): array
    {
        return $monthlyTimes->map(function ($monthlyTimeEntity) {
            return (new self())->setChargeableServiceId($monthlyTimeEntity->chargeable_service_id)
                ->setCode(optional($monthlyTimeEntity->chargeableService)->code)
                ->setChargeableMonth($monthlyTimeEntity->chargeable_month)
                ->setTime($monthlyTimeEntity->total_time);
        })
            ->filter()
            ->toArray();
    }

    public function getChargeableMonth(): Carbon
    {
        return $this->chargeableMonth;
    }

    /**
     * @return int
     */
    public function getChargeableServiceId(): ?int
    {
        return $this->chargeableServiceId;
    }

    /**
     * @return string
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getTime(): int
    {
        return $this->time;
    }

    public function setChargeableMonth(Carbon $chargeableMonth): self
    {
        $this->chargeableMonth = $chargeableMonth;

        return $this;
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
     * @param string $code
     */
    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function setTime(int $time): self
    {
        $this->time = $time;

        return $this;
    }
}
