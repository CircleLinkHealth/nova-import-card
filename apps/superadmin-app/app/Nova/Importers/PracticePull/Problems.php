<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Importers\PracticePull;

use Carbon\Carbon;
use CircleLinkHealth\SharedModels\Entities\PracticePull\Problem;

class Problems extends AbstractImporter
{
    public function clearDuplicates()
    {
        return \DB::statement("
                    DELETE n1
                    FROM practice_pull_problems n1, practice_pull_problems n2
                    WHERE n1.id < n2.id
                    AND n1.mrn = n2.mrn
                    AND n1.name = n2.name
                    AND n1.practice_id = n2.practice_id
                    AND n1.practice_id = {$this->practiceId}
                    AND n2.practice_id = {$this->practiceId}
                ");
    }

    public function model(array $row)
    {
        return new Problem([
            'practice_id' => $this->practiceId,
            'mrn'         => $this->nullOrValue($row['patientid']),
            'name'        => $this->nullOrValue($row['name']),
            'code'        => $this->nullOrValue($row['code']),
            'code_type'   => $this->nullOrValue($row['codetype']),
            'start'       => $row['addeddate'] ? Carbon::parse($row['addeddate']) : null,
            'stop'        => $row['resolvedate'] ? Carbon::parse($row['resolvedate']) : null,
            'status'      => $this->nullOrValue($row['status']),
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
        return array_merge(
            [
            ],
            parent::registerEvents()
        );
    }

    public function rules(): array
    {
        return $this->rules;
    }
}
