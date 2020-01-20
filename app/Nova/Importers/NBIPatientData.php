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

class NBIPatientData extends ReportsErrorsToSlack implements OnEachRow, WithChunkReading, WithValidation, WithHeadingRow
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

    protected function getImportingRules(): array
    {
        return [
            'dob'                 => 'nullable|date',
            'first_name'          => 'required',
            'last_name'           => 'required',
            'mrn'                 => 'required',
            'primary_insurance'   => 'nullable',
            'provider'            => 'nullable',
            'secondary_insurance' => 'nullable',
        ];
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
            ? null
            : $value;
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
     * The message that is displayed before each row error is listed.
     *
     * @return string
     */
    protected function getErrorMessageIntro(): string
    {
        return "The following rows from Importing NBI Patient Data sheet failed to import. See reasons below:";
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

        if ( ! $this->validateRow($args)) {
            ++$this->rowNumber;

            return;
        }

        PatientData::updateOrCreate(
            [
                'mrn' => $row['mrn'],
            ],
            $args
        );

        ++$this->rowNumber;
    }

    public function message(): string
    {
        if (empty($this->importingErrors)){
            return 'Importing Success';
        }

        return "There were some errors during importing. Please check report at {$this->reportToChannel()}";

    }
}
