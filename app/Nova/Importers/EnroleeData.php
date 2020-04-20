<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Importers;

use App\Search\ProviderByName;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;

class EnroleeData implements OnEachRow, WithChunkReading, WithValidation, WithHeadingRow
{
    use Importable;

    protected $attributes;

    protected $modelClass;

    protected $practice;

    protected $resource;

    protected $rules;

    public function __construct($resource, $attributes, $rules, $modelClass)
    {
        $this->resource   = $resource;
        $this->attributes = $attributes;
        $this->rules      = $rules;
        $this->modelClass = $modelClass;
        $this->practice   = $resource->fields->getFieldValue('practice');
    }

    public function chunkSize(): int
    {
        return 200;
    }

    public function onRow(Row $row)
    {
        $row = $row->toArray();

        $provider = ProviderByName::first($row['provider']);

        $row['provider_id'] = optional($provider)->id;
        $row['practice_id'] = optional($this->practice)->id;

        Enrollee::updateOrCreate(
            [
                'mrn' => $row['mrn'],
            ],
            $row
        );
    }

    public function rules(): array
    {
        return $this->rules;
    }
}
