<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Exports;

use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;

class PatientProblemsReport implements FromQuery, ShouldQueue
{
    use Exportable;
    /**
     * @var Practice
     */
    private $practice;

    public function forPractice(Practice $practice)
    {
        $this->practice = $practice;

        return $this;
    }

    /**
     * @return Builder
     */
    public function query()
    {
        return User::ofPractice($this->practice)->ofType('participant')->with([
            'patientInfo' => function ($q) {
                $q->select('mrn_number', 'birth_date');
            },
            'ccdProblems' => function ($q) {
                $q->select('id', 'name')->distinct()->with(['icd10Codes', 'cpmProblem']);
            },
        ])->select('id', 'display_name');
    }
}
