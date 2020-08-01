<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Exports\PracticeReports;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Problem;
use Illuminate\Database\Eloquent\Builder;

class PatientProblemsReportBase extends BasePracticeReport
{
    public function filename(): string
    {
        if ( ! $this->filename) {
            $generatedAt    = now()->toDateTimeString();
            $this->filename = "patients_with_problems_report_generated_at_$generatedAt.csv";
        }

        return $this->filename;
    }

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

    public function mediaCollectionName(): string
    {
        return "{$this->practice->name}_patients_with_problems_reports";
    }

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
