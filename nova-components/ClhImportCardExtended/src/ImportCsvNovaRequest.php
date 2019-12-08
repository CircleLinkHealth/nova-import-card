<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Circlelinkhealth\ClhImportCardExtended;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Http\Requests\NovaRequest;

class ImportCsvNovaRequest extends NovaRequest
{
    private $fields = [];

    public function authorize()
    {
        return Auth::check();
    }

    public function newResource()
    {
        $resource = parent::newResource();

        if (is_object($resource)) {
            $resource->fields   = $this->getFieldsFromRequest();
            $resource->fileName = $this->file->getClientOriginalName();
        }

        return $resource;
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'fields' => $this->getRulesForFields(),
            'file'   => 'required|file',
        ]);
    }

    /**
     * @return mixed
     */
    private function getFieldsFromRequest()
    {
        if ( ! empty($this->fields)) {
            return $this->fields;
        }

        foreach ($this->getFieldsInputFromRequest() as $field) {
            $this->fields[$field->indexName] = $this->getInputValueOrModel($field);
        }

        return $this->fields;
    }

    private function getFieldsInputFromRequest(): array
    {
        return json_decode($this->input('fields'));
    }

    private function getFieldValue($field)
    {
        return is_a($field->value, 'stdClass') && isset($field->value->value)
            ? $field->value->value
            : $field->value;
    }

    private function getInputValueOrModel($field)
    {
        if ($this->isModel($field)) {
            return $field->model::find($this->getFieldValue($field));
        }

        return $this->getFieldValue($field);
    }

    private function getRulesForFields()
    {
        return [
            'required',
            function ($attribute, $value, $fail) {
                foreach (json_decode($value) as $field) {
                    if ( ! $field->nullable) {
                        if ( ! $this->getFieldValue($field)) {
                            return $fail('Field '.$field->indexName.' is required.');
                        }

                        if ($this->isModel($field)) {
                            if ( ! $this->getInputValueOrModel($field)) {
                                return $fail('Model '.$field->model.'not found');
                            }
                        }
                    }
                }
            },
        ];
    }

    private function isModel($field): bool
    {
        return isset($field->model) && ! is_null($field->model) && is_subclass_of($field->model, Model::class);
    }
}
