<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Circlelinkhealth\ClhImportCardExtended;

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

        $this->fields = (new Fields())->setInputFields($this->getFieldsInputFromRequest());

        return $this->fields;
    }

    private function getFieldsInputFromRequest(): array
    {
        return json_decode($this->input('fields'));
    }

    private function getRulesForFields()
    {
        return [
            'required',
            function ($attribute, $value, $fail) {
                foreach (json_decode($value) as $field) {
                    $field = new InputField($field);
                    $field->validate();

                    if ( ! $field->isNullable()) {
                        if ( ! $field->getFieldValue()) {
                            return $fail('Field '.$field->getName().' is required.');
                        }
                    }

                    if ($field->isModel()) {
                        if ( ! $field->getInputValueOrModel()) {
                            return $fail('Model '.$field->getModelClass().'with key '.$field->getModelKey().' not found');
                        }
                    }
                }
            },
        ];
    }
}
