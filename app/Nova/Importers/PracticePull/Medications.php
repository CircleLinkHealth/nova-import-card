<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Importers\PracticePull;

use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class Medications implements ToModel, WithChunkReading, WithHeadingRow, WithBatchInserts, ShouldQueue
{
    use Importable;

    /**
     * @var int
     */
    private $practiceId;

    /**
     * Medications constructor.
     */
    public function __construct(int $practiceId)
    {
        ini_set('upload_max_filesize', '200M');
        ini_set('post_max_size', '200M');
        ini_set('max_input_time', 900);
        ini_set('max_execution_time', 900);

        $this->practiceId = $practiceId;
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
        return new \CircleLinkHealth\SharedModels\Entities\PracticePull\Medication([
            'practice_id' => $this->practiceId,
            'mrn'         => $this->nullOrValue($row['patientid']),
            'name'        => $this->nullOrValue($row['rx']),
            'sig'         => $this->nullOrValue($row['sig']),
            'start'       => $row['startdate'] ? Carbon::parse($row['startdate']) : null,
            'stop'        => $row['stopdate'] ? Carbon::parse($row['stopdate']) : null,
            'status'      => $this->nullOrValue($row['medstatus']),
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
