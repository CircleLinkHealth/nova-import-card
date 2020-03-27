<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Relationships;

use App\Algorithms\Invoicing\AlternativeCareTimePayableCalculator;
use Carbon\Carbon;

class BillableCPMPatientRelations
{
    /**
     * @var Carbon
     */
    protected $month;
    
    public function __construct(Carbon $month)
    {
        $this->month = $month;
    }
    
    const DEFAULT_RELATIONSHIPS = [
        'careTeamMembers',
        'patientInfo',
        'primaryPractice',
        'patientSummaries',
    ];
    
    public static function getDefaultWith(Carbon $month): array
    {
        return (new static($month))->get(self::DEFAULT_RELATIONSHIPS);
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
                'careTeamMembers'  => function ($q) {
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
