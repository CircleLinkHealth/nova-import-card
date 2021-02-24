<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\DTO;

class InboundCallbackNameFields
{
    private string $callerNameField;
    private string $fromNameField;
    private string $ptnNameField;

    public function __construct(string $callerNameField, string $fromNameField, string $ptnNameField)
    {
        $this->callerNameField = $callerNameField;
        $this->fromNameField   = $fromNameField;
        $this->ptnNameField    = $ptnNameField;
    }

    public function allNameFields(): array
    {
        return [
            $this->callerNameField,
            $this->fromNameField,
            $this->ptnNameField,
        ];
    }

    public function callerNameField(): string
    {
        return $this->callerNameField;
    }

    public function fromNameField(): string
    {
        return $this->fromNameField;
    }

    public function ptnNameField(): string
    {
        return $this->ptnNameField;
    }
}
