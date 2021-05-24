<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecord\Templates\Resources;

use Illuminate\Contracts\Support\Arrayable;

class Medication implements Arrayable
{
    public ?string $administrationCode               = null;
    public ?string $administrationCodeSystem         = null;
    public ?string $administrationCodeSystemName     = null;
    public ?string $administrationName               = null;
    public ?string $dateRangeEnd                     = null;
    public ?string $dateRangeStart                   = null;
    public ?string $doseQuantityUnit                 = null;
    public ?string $doseQuantityValue                = null;
    public ?string $preconditionCode                 = null;
    public ?string $preconditionCodeSystem           = null;
    public ?string $preconditionName                 = null;
    public ?string $prescriberOrganization           = null;
    public ?string $prescriberPerson                 = null;
    public ?string $productCode                      = null;
    public ?string $productCodeSystem                = null;
    public ?string $productName                      = null;
    public ?string $productText                      = null;
    public ?string $productTranslationCode           = null;
    public ?string $productTranslationCodeSystem     = null;
    public ?string $productTranslationCodeSystemName = null;
    public ?string $productTranslationName           = null;
    public ?string $rateQuantityUnit                 = null;
    public ?string $rateQuantityValue                = null;
    public ?string $reasonCode                       = null;
    public ?string $reasonCodeSystem                 = null;
    public ?string $reasonName                       = null;
    public ?string $reference                        = null;
    public ?string $referenceSig                     = null;
    public ?string $referenceTitle                   = null;
    public ?string $routeCode                        = null;
    public ?string $routeCodeSystem                  = null;
    public ?string $routeCodeSystemName              = null;
    public ?string $routeName                        = null;
    public ?string $schedulePeriodUnit               = null;
    public ?string $schedulePeriodValue              = null;
    public ?string $scheduleType                     = null;
    public ?string $status                           = null;
    public ?string $text                             = null;
    public ?string $vehicleCode                      = null;
    public ?string $vehicleCodeSystem                = null;
    public ?string $vehicleCodeSystemName            = null;
    public ?string $vehicleName                      = null;

    public function toArray()
    {
        return [
            'reference'       => $this->reference,
            'reference_title' => $this->referenceTitle,
            'reference_sig'   => $this->referenceSig,
            'date_range'      => [
                'start' => $this->dateRangeStart,
                'end'   => $this->dateRangeEnd,
            ],
            'status'  => $this->status,
            'text'    => $this->text,
            'product' => [
                'name'        => $this->productName,
                'code'        => $this->productCode,
                'code_system' => $this->productCodeSystem,
                'text'        => $this->productText,
                'translation' => [
                    'name'             => $this->productTranslationName,
                    'code'             => $this->productTranslationCode,
                    'code_system'      => $this->productTranslationCodeSystem,
                    'code_system_name' => $this->productTranslationCodeSystemName,
                ],
            ],
            'dose_quantity' => [
                'value' => $this->doseQuantityValue,
                'unit'  => $this->doseQuantityUnit,
            ],
            'rate_quantity' => [
                'value' => $this->rateQuantityValue,
                'unit'  => $this->rateQuantityUnit,
            ],
            'precondition' => [
                'name'        => $this->preconditionName,
                'code'        => $this->preconditionCode,
                'code_system' => $this->preconditionCodeSystem,
            ],
            'reason' => [
                'name'        => $this->reasonName,
                'code'        => $this->reasonCode,
                'code_system' => $this->reasonCodeSystem,
            ],
            'route' => [
                'name'             => $this->routeName,
                'code'             => $this->routeCode,
                'code_system'      => $this->routeCodeSystem,
                'code_system_name' => $this->routeCodeSystemName,
            ],
            'schedule' => [
                'type'         => $this->scheduleType,
                'period_value' => $this->schedulePeriodValue,
                'period_unit'  => $this->schedulePeriodUnit,
            ],
            'vehicle' => [
                'name'             => $this->vehicleName,
                'code'             => $this->vehicleCode,
                'code_system'      => $this->vehicleCodeSystem,
                'code_system_name' => $this->vehicleCodeSystemName,
            ],
            'administration' => [
                'name'             => $this->administrationName,
                'code'             => $this->administrationCode,
                'code_system'      => $this->administrationCodeSystem,
                'code_system_name' => $this->administrationCodeSystemName,
            ],
            'prescriber' => [
                'organization' => $this->prescriberOrganization,
                'person'       => $this->prescriberPerson,
            ],
        ];
    }
}
