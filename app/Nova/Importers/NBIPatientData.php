<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Importers;

use App\Models\PatientData\NBI\PatientData;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;
use Sparclex\NovaImportCard\ImportNovaRequest;

class NBIPatientData implements OnEachRow, WithChunkReading, WithValidation, WithHeadingRow
{
    use Importable;

    protected $attributes;

    protected $modelClass;

    protected $rules;

    public function __construct($resource, $attributes, $rules, $modelClass)
    {
        $this->resource   = $resource;
        $this->attributes = $attributes;
        $this->rules      = $rules;
        $this->modelClass = $modelClass;
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 200;
    }

    public function model(array $row)
    {
        [$model, $callbacks] = $this->resource::fill(
            new ImportNovaRequest($row),
            $this->resource::newModel()
        );

        return $model;
    }

    /**
     * @param Row $row
     */
    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $row      = $row->toArray();

        return PatientData::updateOrCreate(
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
