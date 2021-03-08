<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Circlelinkhealth\ClhImportCardExtended;

class Fields
{
    protected $inputFields = [];

    public function getFieldValue($name)
    {
        return $this->inputFields[strtolower($name)];
    }

    public function setInputFields(array $fieldsFromRequest)
    {
        foreach ($fieldsFromRequest as $field) {
            $inputField                                            = new InputField($field);
            $this->inputFields[strtolower($inputField->getName())] = $inputField->getInputValueOrModel();
        }

        return $this;
    }
}
