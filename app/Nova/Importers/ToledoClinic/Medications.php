<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Importers\ToledoClinic;

use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class Medications implements ToModel, WithChunkReading, WithHeadingRow, WithBatchInserts, ShouldQueue
{
    use Importable;

    public function __construct()
    {
        ini_set('upload_max_filesize', '200M');
        ini_set('post_max_size', '200M');
        ini_set('max_input_time', 900);
        ini_set('max_execution_time', 900);
    }

    public function batchSize(): int
    {
        return 400;
    }

    public function chunkSize(): int
    {
        return 400;
    }

    public function model(array $row)
    {
        return new \App\Models\ToledoClinic\Medications([
            'patient_id' => $this->nullOrValue($row['patientid']),
            'name'       => $this->nullOrValue($row['rx']),
            'sig'        => $this->nullOrValue($row['sig']),
            'start'      => $this->nullOrValue($row['startdate']),
            'stop'       => $this->nullOrValue($row['stopdate']),
            'status'     => $this->nullOrValue($row['medstatus']),
        ]);
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
            '########',
            '#N/A',
            '-',
        ];
    }

    public function rules(): array
    {
        return $this->rules;
    }
}
