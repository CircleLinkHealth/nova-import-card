<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Importers\PracticePull;

use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\AfterImport;

class Allergies implements ToModel, WithChunkReading, WithHeadingRow, WithBatchInserts, ShouldQueue, WithEvents
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
        return new \CircleLinkHealth\SharedModels\Entities\PracticePull\Allergy([
                                                                                    'practice_id' => $this->practiceId,
                                                                                    'mrn'         => $this->nullOrValue($row['patientid']),
                                                                                    'name'        => $this->nullOrValue($row['name']),
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
                    FROM practice_pull_allergies n1, practice_pull_allergies n2
                    WHERE n1.id < n2.id
                    AND n1.mrn = n2.mrn
                    AND n1.practice_id = n2.practice_id
                    AND n1.practice_id = {$this->practiceId}
                    AND n2.practice_id = {$this->practiceId}
                ");
            },
        ];
    }
}
