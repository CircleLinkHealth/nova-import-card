<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecord\Templates\Resources;

use Illuminate\Contracts\Support\Arrayable;

class Allergy implements Arrayable
{
    public string $allergenCode               = '';
    public string $allergenCodeSystem         = '';
    public string $allergenCodeSystemName     = '';
    public string $allergenName               = '';
    public string $code                       = '';
    public string $codeSystem                 = '';
    public string $codeSystemName             = '';
    public string $dateRangeEnd               = '';
    public string $dateRangeStart             = '';
    public string $name                       = '';
    public string $reactionCode               = '';
    public string $reactionCodeSystem         = '';
    public string $reactionName               = '';
    public string $reactionTypeCode           = '';
    public string $reactionTypeCodeSystem     = '';
    public string $reactionTypeCodeSystemName = '';
    public string $reactionTypeName           = '';
    public string $severity                   = '';
    public string $status                     = '';

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
