<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecord\Templates\Resources;

use Illuminate\Contracts\Support\Arrayable;

class Allergy implements Arrayable
{
    public ?string $allergenCode               = null;
    public ?string $allergenCodeSystem         = null;
    public ?string $allergenCodeSystemName     = null;
    public ?string $allergenName               = null;
    public ?string $code                       = null;
    public ?string $codeSystem                 = null;
    public ?string $codeSystemName             = null;
    public ?string $dateRangeEnd               = null;
    public ?string $dateRangeStart             = null;
    public ?string $name                       = null;
    public ?string $reactionCode               = null;
    public ?string $reactionCodeSystem         = null;
    public ?string $reactionName               = null;
    public ?string $reactionTypeCode           = null;
    public ?string $reactionTypeCodeSystem     = null;
    public ?string $reactionTypeCodeSystemName = null;
    public ?string $reactionTypeName           = null;
    public ?string $severity                   = null;
    public ?string $status                     = null;

    public function toArray()
    {
        return [
            'date_range' => [
                'start' => $this->dateRangeStart,
                'end'   => $this->dateRangeEnd,
            ],
            'name'             => $this->name,
            'code'             => $this->code,
            'code_system'      => $this->codeSystem,
            'code_system_name' => $this->codeSystemName,
            'status'           => $this->status,
            'severity'         => $this->severity,
            'reaction'         => [
                'name'        => $this->reactionName,
                'code'        => $this->reactionCode,
                'code_system' => $this->reactionCodeSystem,
            ],
            'reaction_type' => [
                'name'             => $this->reactionTypeName,
                'code'             => $this->reactionTypeCode,
                'code_system'      => $this->reactionTypeCodeSystem,
                'code_system_name' => $this->reactionTypeCodeSystemName,
            ],
            'allergen' => [
                'name'             => $this->allergenName,
                'code'             => $this->allergenCode,
                'code_system'      => $this->allergenCodeSystem,
                'code_system_name' => $this->allergenCodeSystemName,
            ],
        ];
    }
}
