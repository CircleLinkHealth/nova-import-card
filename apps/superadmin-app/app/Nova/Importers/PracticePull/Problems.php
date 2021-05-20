<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Importers\PracticePull;

use Carbon\Carbon;
use CircleLinkHealth\Core\Jobs\UpdateOrCreateInDb;
use CircleLinkHealth\SharedModels\Entities\PracticePull\Problem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class Problems implements WithChunkReading, WithHeadingRow, ShouldQueue, OnEachRow
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

    public function chunkSize(): int
    {
        return 500;
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

    public function onRow(Row $row)
    {
        UpdateOrCreateInDb::dispatch(
            Problem::class,
            [
                'practice_id' => $this->practiceId,
                'mrn'         => $this->nullOrValue($row['patientid']),
                'name'        => $this->nullOrValue($row['name']),
                'code'        => $this->nullOrValue($row['code']),
                'code_type'   => $this->nullOrValue($row['codetype']),
                'start'       => $row['addeddate']
                    ? Carbon::parse($row['addeddate'])
                    : null,
            ],
            [
                'stop' => $row['resolvedate']
                    ? Carbon::parse($row['resolvedate'])
                    : null,
                'status' => $this->nullOrValue($row['status']),
            ]
        );
    }

    public function rules(): array
    {
        return $this->rules;
    }
}
