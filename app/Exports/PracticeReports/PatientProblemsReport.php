<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Exports\PracticeReports;

use App\Contracts\Reports\PracticeDataExport;
use CircleLinkHealth\CarePlanModels\Entities\Problem;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Builder;

class PatientProblemsReport extends PracticeReport
{
    public function createMedia($mediaCollectionName = null): PracticeDataExport
    {
        if ( ! $this->media) {
            if ( ! $mediaCollectionName) {
                $mediaCollectionName = "{$this->practice->name}_patients_with_problems_reports";
            }

            $this->store($this->filename(), self::STORE_TEMP_REPORT_ON_DISK);

            $this->media = $this->practice->addMedia($this->fullPath())->toMediaCollection($mediaCollectionName);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function filename(): string
    {
        if ( ! $this->filename) {
            $generatedAt    = now()->toDateTimeString();
            $this->filename = "patients_with_problems_report_generated_at_$generatedAt.csv";
        }

        return $this->filename;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Name',
            'MRN',
            'DOB',
            'Condition',
            'ICD-10',
        ];
    }

    /**
     * @param mixed $row
     *
     * @return array
     */
    public function map($row): array
    {
        $demographics = [
            'name' => $row->display_name,
            'mrn'  => $row->getMrnNumber(),
            'dob'  => $row->patientInfo->dob(),
        ];

        if ($row->ccdProblems->isEmpty()) {
            return array_merge(
                $demographics,
                [
                    'condition' => 'N/A',
                    'icd10'     => 'N/A',
                ]
            );
        }

        return $row->ccdProblems->map(
            function (Problem $problem) use ($demographics) {
                return array_merge(
                    $demographics,
                    [
                        'condition' => $problem->name,
                        'icd10'     => $problem->icd10Code() ?? 'N/A',
                    ]
                );
            }
        )->all();
    }

    /**
     * @return Builder
     */
    public function query(): Builder
    {
        return User::ofPractice($this->practice)
                   ->ofType('participant')
                   ->has('patientInfo')
                   ->with(
                       [
                           'patientInfo' => function ($q) {
                               $q->select('mrn_number', 'birth_date', 'id', 'user_id');
                           },
                           'ccdProblems' => function ($q) {
                               $q->select('id', 'name', 'cpm_problem_id', 'patient_id')->with(
                                   ['icd10Codes', 'cpmProblem']
                               );
                           },
                       ]
                   )->select('id', 'display_name');
    }
}
