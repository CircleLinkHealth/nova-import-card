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

    /**
     * InboundCallbackNameFields constructor.
     */
    public function __construct(string $callerNameField, string $fromNameField, string $ptnNameField)
    {
        $this->callerNameField = $callerNameField;
        $this->fromNameField   = $fromNameField;
        $this->ptnNameField    = $ptnNameField;
    }

    /**
     * @return array
     */
    public function allNameFields()
    {
        return [
            $this->callerNameField,
            $this->fromNameField,
            $this->ptnNameField,
        ];
    }

    /**
     * @return string
     */
    public function callerNameField()
    {
        return $this->callerNameField;
    }

    /**
     * @return string
     */
    public function fromNameField()
    {
        return $this->fromNameField;
    }

    /**
     * @return string
     */
    public function ptnNameField()
    {
        return $this->ptnNameField;
    }
}
