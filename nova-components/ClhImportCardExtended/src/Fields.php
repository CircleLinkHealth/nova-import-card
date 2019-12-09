<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Circlelinkhealth\ClhImportCardExtended;

class Fields
{
    protected $fields;

    //make like a collection class
    public function __construct(array $fields)
    {
        $this->fields = collect($fields);
    }

    public function getField($name)
    {
        return new InputField($this->fields->filter(function ($field) use ($name) {
            return $field->indexName == $name;
        })->first());
    }

    public function getFieldValue($name)
    {
        $field = $this->getField($name);

        return $field ? $field->getFieldValue() : null;
    }
}
