<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Filters;

use CircleLinkHealth\Eligibility\MedicalRecordImporter\SnomedToCpmIcdMap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Filters\Filter;

class CodesFlaggedForReview extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    /**
     * Apply the filter to the given query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed                                 $value
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        $codes = DB::table((new SnomedToCpmIcdMap())->getTable())
            ->selectRaw("$value, count(distinct cpm_problem_id) as count")
            ->whereNotNull($value)
            ->where($value, '!=', '')
            ->where($value, '!=', 0)
            ->groupBy($value)
            ->havingRaw('count > 1')
            ->pluck($value)
            ->all();

        return $query->whereIn($value, $codes);
    }

    /**
     * Get the filter's available options.
     *
     * @return array
     */
    public function options(Request $request)
    {
        return [
            'SNOMED' => 'snomed_code',
            'ICD-9'  => 'icd_9_code',
            'ICD-10' => 'icd_10_code',
        ];
    }
}
