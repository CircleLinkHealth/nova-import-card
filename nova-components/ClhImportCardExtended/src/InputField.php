<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ClhImportCardExtended;

use Illuminate\Database\Eloquent\Model;
use Validator;

/**
 * Class InputField.
 */
class InputField
{
    /**
     * @var \stdClass
     */
    protected $field;

    public function __construct(\stdClass $field)
    {
        $this->field = $field;
    }

    public function getFieldValue()
    {
        return is_a($this->field->value, 'stdClass') && isset($this->field->value->value)
            ? $this->field->value->value
            : $this->field->value;
    }

    public function getInputValueOrModel()
    {
        if ($this->isModel()) {
            return $this->field->model::where($this->getModelKey(), $this->getFieldValue())->first();
        }

        return $this->getFieldValue();
    }

    public function getModelClass()
    {
        return $this->field->model;
    }

    public function getModelKey()
    {
        return $this->field->modelKey ?: 'id';
    }

    public function getName()
    {
        return $this->field->indexName;
    }

    public function isModel(): bool
    {
        return isset($this->field->model) && ! is_null($this->field->model) && is_subclass_of($this->field->model, Model::class);
    }

    public function isNullable()
    {
        return $this->field->nullable;
    }

    public function validate()
    {
        return Validator::make(['value' => $this->getFieldValue()], [
            'value' => $this->getRules(),
        ])->validate();
    }

    private function getRules()
    {
        return property_exists($this->field, 'inputRules') ? $this->field->inputRules : [];
    }
}
