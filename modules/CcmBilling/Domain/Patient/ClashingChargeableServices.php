<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Patient;

use CircleLinkHealth\Customer\Entities\ChargeableService;

class ClashingChargeableServices
{
    const CLASHES = [
        ChargeableService::PCM => [
            ChargeableService::GENERAL_CARE_MANAGEMENT,
            ChargeableService::CCM,
            ChargeableService::CCM_PLUS_40,
            ChargeableService::CCM_PLUS_60,
            ChargeableService::RPM,
            ChargeableService::RPM40,
            ChargeableService::RPM60,
        ],
        ChargeableService::RPM => [
            ChargeableService::GENERAL_CARE_MANAGEMENT,
        ],
        ChargeableService::RPM40 => [
            ChargeableService::GENERAL_CARE_MANAGEMENT,
        ],
        ChargeableService::RPM60 => [
            ChargeableService::GENERAL_CARE_MANAGEMENT,
        ],
        ChargeableService::GENERAL_CARE_MANAGEMENT => [
        ],
        ChargeableService::CCM => [
            ChargeableService::RPM,
            ChargeableService::RPM40,
            ChargeableService::RPM60,
            ChargeableService::GENERAL_CARE_MANAGEMENT,
        ],
        ChargeableService::CCM_PLUS_40 => [
            ChargeableService::RPM,
            ChargeableService::RPM40,
            ChargeableService::RPM60,
            ChargeableService::GENERAL_CARE_MANAGEMENT,
        ],
        ChargeableService::CCM_PLUS_60 => [
            ChargeableService::RPM,
            ChargeableService::RPM40,
            ChargeableService::RPM60,
            ChargeableService::GENERAL_CARE_MANAGEMENT,
        ],
        ChargeableService::BHI => [
            ChargeableService::RPM,
            ChargeableService::RPM40,
            ChargeableService::RPM60,
            ChargeableService::GENERAL_CARE_MANAGEMENT,
        ],
        ChargeableService::AWV_INITIAL => [
        ],
        ChargeableService::AWV_SUBSEQUENT => [
        ],
    ];

    public static function getClashesOfService(string $service): array
    {
        return self::CLASHES[$service];
    }

    public static function getProcessorsForClashesOfService(string $code): array
    {
        return collect(self::getClashesOfService($code))
            ->map(fn ($service) => ChargeableService::getProcessorForCode($service))
            ->filter()
            ->toArray();
    }

    public static function getProcessorsServiceIsClashFor(string $code): array
    {
        return collect(self::serviceIsClashFor($code))
            ->map(fn ($service) => ChargeableService::getProcessorForCode($service))
            ->filter()
            ->toArray();
    }

    public static function serviceIsClashFor(string $service): array
    {
        return collect(self::CLASHES)->filter(function ($clashes) use ($service) {
            return in_array($service, $clashes);
        })
            ->map(function ($value, $key) {
                return $key;
            })
            ->toArray();
    }
}
