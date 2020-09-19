<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Relationships;

use CircleLinkHealth\Customer\NurseTimeAlgorithms\AlternativeCareTimePayableCalculator;
use Carbon\Carbon;

class BillableCPMPatientRelations
{
    const DEFAULT_RELATIONSHIPS = [
        'careTeamMembers',
        'patientInfo',
        'primaryPractice',
        'patientSummaries',
    ];
    /**
     * @var Carbon
     */
    protected $month;

    public function __construct(Carbon $month)
    {
        $this->month = $month;
    }

    public function get(
        array $with = [
            'careTeamMembers',
            'patientInfo',
            'primaryPractice',
            'patientSummaries',
        ]
    ): array {
        return collect(
            [
                'patientSummaries' => $this->getPatientSummariesCallback(),
                'patientInfo',
                'primaryPractice',
                'careTeamMembers' => function ($q) {
                    $q->where('type', '=', 'billing_provider');
                },
            ]
        )->reject(
            function ($item, $key) use ($with) {
                if (in_array($key, $with)) {
                    return false;
                }
                if (in_array($item, $with)) {
                    return false;
                }

                return true;
            }
        )->all();
    }

    public static function getDefaultWith(Carbon $month): array
    {
        return (new static($month))->get(self::DEFAULT_RELATIONSHIPS);
    }

    private function getPatientSummariesCallback()
    {
        return function ($query) {
            $query->where('month_year', $this->month)
                ->where(
                    'total_time',
                    '>=',
                    AlternativeCareTimePayableCalculator::MONTHLY_TIME_TARGET_IN_SECONDS
                )
                ->where('no_of_successful_calls', '>=', 1)
                ->with('chargeableServices')
                ->with('attestedProblems.cpmProblem')
                ->with('attestedProblems.icd10Codes');
        };
    }
}
