<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Importers;

use CircleLinkHealth\Eligibility\Entities\PatientData;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;

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

    /**
     * Returns null if value means N/A or equivalent. Otherwise returns the value passed to it.
     *
     * @param string $value
     *
     * @return string|null
     */
    public function nullOrValue($value)
    {
        return empty($value) || in_array($value, $this->nullValues())
            ? null : $value;
    }

    /**
     * If the value of a cell is any of these we shall consider it null.
     *
     * @return array
     */
    public function nullValues()
    {
        return [
            'NA: In CPM',
            'N/A',
        ];
    }

    /**
     * @param Row $row
     */
    public function onRow(Row $row)
    {
        $this->persistRow($row->toArray());
    }

    public function rules(): array
    {
        return $this->rules;
    }

    private function persistRow(array $row)
    {
        $args = [
            'dob'                 => $this->nullOrValue($row['dob']),
            'first_name'          => $this->nullOrValue($row['first_name']),
            'last_name'           => $this->nullOrValue($row['last_name']),
            'mrn'                 => $this->nullOrValue($row['mrn']),
            'primary_insurance'   => $this->nullOrValue($row['primary_insurance']),
            'provider'            => $this->nullOrValue($row['provider']),
            'secondary_insurance' => $this->nullOrValue($row['secondary_insurance']),
        ];

        //validate args + add error handling

        if ( ! empty($args['mrn']) && ! empty($args['first_name']) && ! empty($args['last_name'])) {
            return PatientData::updateOrCreate(
                [
                    'mrn' => $row['mrn'],
                ],
                $args
            );
        }
    }
}
