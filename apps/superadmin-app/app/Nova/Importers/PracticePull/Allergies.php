<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Importers\PracticePull;

use Maatwebsite\Excel\Events\AfterImport;

class Allergies extends AbstractImporter
{
    public function model(array $row)
    {
        return new \CircleLinkHealth\SharedModels\Entities\PracticePull\Allergy([
            'practice_id' => $this->practiceId,
            'mrn'         => $this->nullOrValue($row['patientid']),
            'name'        => $this->nullOrValue($row['name']),
        ]);
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
                    AND n1.name = n2.name
                    AND n1.practice_id = n2.practice_id
                    AND n1.practice_id = {$this->practiceId}
                    AND n2.practice_id = {$this->practiceId}
                ");
            },
        ];
    }
}
