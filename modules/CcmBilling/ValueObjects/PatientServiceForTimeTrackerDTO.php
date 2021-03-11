<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\ValueObjects;


use Illuminate\Support\Collection;

class PatientServiceForTimeTrackerDTO
{
    public ?string $chargeable_service_code;
    public ?int $chargeable_service_id;
    public ?string $chargeable_service_display_name;
    public ?int $patient_id;

    /**
     * @return string
     */
    public function getChargeableServiceCode(): string
    {
        return $this->chargeable_service_code;
    }

    /**
     * @param string $chargeable_service_code
     *
     * @return PatientServiceForTimeTrackerDTO
     */
    public function setChargeableServiceCode(string $chargeable_service_code): PatientServiceForTimeTrackerDTO
    {
        $this->chargeable_service_code = $chargeable_service_code;

        return $this;
    }

    /**
     * @return int
     */
    public function getChargeableServiceId(): int
    {
        return $this->chargeable_service_id;
    }

    /**
     * @param int $chargeable_service_id
     *
     * @return PatientServiceForTimeTrackerDTO
     */
    public function setChargeableServiceId(int $chargeable_service_id): PatientServiceForTimeTrackerDTO
    {
        $this->chargeable_service_id = $chargeable_service_id;

        return $this;
    }

    /**
     * @return string
     */
    public function getChargeableServiceDisplayName(): string
    {
        return $this->chargeable_service_display_name;
    }

    /**
     * @param string $chargeable_service_display_name
     *
     * @return PatientServiceForTimeTrackerDTO
     */
    public function setChargeableServiceDisplayName(string $chargeable_service_display_name
    ): PatientServiceForTimeTrackerDTO {
        $this->chargeable_service_display_name = $chargeable_service_display_name;

        return $this;
    }

    /**
     * @return int
     */
    public function getPatientId(): int
    {
        return $this->patient_id;
    }

    /**
     * @param int $patient_id
     *
     * @return PatientServiceForTimeTrackerDTO
     */
    public function setPatientId(int $patient_id): PatientServiceForTimeTrackerDTO
    {
        $this->patient_id = $patient_id;

        return $this;
    }

    /**
     * @return int
     */
    public function getTotalTime(): int
    {
        return $this->total_time;
    }

    /**
     * @param int $total_time
     *
     * @return PatientServiceForTimeTrackerDTO
     */
    public function setTotalTime(int $total_time): PatientServiceForTimeTrackerDTO
    {
        $this->total_time = $total_time;

        return $this;
    }
    public int $total_time = 0;

    public static function fromArray(array $array):self
    {
        return (new static())->setPatientId($array['patient_id'] ?? null)
            ->setChargeableServiceId($array['chargeable_service_id'] ?? null)
            ->setChargeableServiceCode($array['chargeable_service_code'] ?? null)
            ->setChargeableServiceDisplayName($array['chargeable_service_display_name'] ?? null)
            ->setTotalTime($array['total_time'] ?? 0);
    }

    public static function collectionFromDto(PatientMonthlyBillingDTO $dto): Collection
    {
        $collection = collect();
        foreach ($dto->getPatientServices() as $service){
            $collection->push(
                self::fromArray(
                    [
                        'patient_id' => $dto->getPatientId(),
                        'chargeable_service_id' => $service->getChargeableServiceId(),
                        'chargeable_service_code' => $service->getCode(),
                        'chargeable_service_display_name' => $service->getDisplayName(),
                        'total_time' => optional(collect($dto->getPatientTimes())->whereNull('chargeable_service_id')->first())->total_time ?? 0
                    ])
            );
        }
        if ($collection->isEmpty()){
            $collection->push(
                self::fromArray([
                    'patient_id' => $dto->getPatientId(),
                    'chargeable_service_id' => -1,
                    'chargeable_service_display_name' => 'NONE',
                    'chargeable_service_code' => 'NONE',
                    'total_time' => optional(collect($dto->getPatientTimes())->whereNull('chargeable_service_id')->first())->total_time ?? 0
                ])
            );
        }
        return $collection;
    }

}