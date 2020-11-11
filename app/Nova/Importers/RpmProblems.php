<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Importers;

use CircleLinkHealth\Eligibility\Entities\RpmProblem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class RpmProblems implements WithChunkReading, OnEachRow, WithHeadingRow, ShouldQueue, WithEvents
{
    use Importable;
    use RegistersEventListeners;

    private $fileName;

    /**
     * @var int
     */
    private $practiceId;

    public function __construct(int $practiceId, $fileName)
    {
        $this->practiceId = $practiceId;
        $this->fileName   = $fileName;
    }

    /**
     * {@inheritdoc}
     */
    public function chunkSize(): int
    {
        return 100;
    }

    public function message(): string
    {
        return 'File queued for importing.';
    }

    /**
     * {@inheritdoc}
     */
    public function onRow(Row $row)
    {
        $row = $row->toArray();

        RpmProblem::updateOrCreate([
            'practice_id' => $this->practiceId,
            'code_type'   => $row['code_type'],
            'code'        => $row['code'],
            'description' => $row['description'],
        ]);
    }
}
