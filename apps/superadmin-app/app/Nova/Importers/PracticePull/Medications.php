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
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\AfterImport;

class Medications implements ToModel, WithChunkReading, WithHeadingRow, WithBatchInserts, ShouldQueue, WithEvents
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
        $this->practiceId = $practiceId;
    }

    public function batchSize(): int
    {
        return 80;
    }

    public function chunkSize(): int
    {
        return 80;
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

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function (AfterImport $event) {
                $dupsDeleted = \DB::statement("
                    DELETE n1
                    FROM practice_pull_medications n1, practice_pull_medications n2
                    WHERE n1.id < n2.id
                    AND n1.mrn = n2.mrn
                    AND n1.name = n2.name
                    AND n1.practice_id = n2.practice_id
                    AND n1.practice_id = {$this->practiceId}
                    AND n2.practice_id = {$this->practiceId}
                ");
            },
        ];
    }

    public function rules(): array
    {
        return $this->rules;
    }
}
