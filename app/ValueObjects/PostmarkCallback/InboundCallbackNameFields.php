<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\ValueObjects\PostmarkCallback;

class InboundCallbackNameFields
{
    private string $callerNameField;
    private string $fromNameField;
    private string $ptnNameField;
    
    /**
     * InboundCallbackNameFields constructor.
     * @param string $callerNameField
     * @param string $fromNameField
     * @param string $ptnNameField
     */
    public function __construct(string $callerNameField, string $fromNameField, string $ptnNameField)
    {
        $this->callerNameField = $callerNameField;
        $this->fromNameField = $fromNameField;
        $this->ptnNameField = $ptnNameField;
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
}
