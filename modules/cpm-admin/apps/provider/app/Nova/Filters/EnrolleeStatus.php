<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Filters;

use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class EnrolleeStatus extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'boolean-filter';

    /**
     * The displayable name of the filter.
     *
     * @var string
     */
    public $name = 'Filter Patients by Status';

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
        $statuses = [];
        foreach ($value as $status => $bool) {
            if (true === $bool) {
                $statuses[] = $status;
            }
        }

        if ( ! empty($statuses)) {
            return $query->whereIn('status', $statuses);
        }

        return $query;
    }

    /**
     * Get the filter's available options.
     *
     * @return array
     */
    public function options(Request $request)
    {
        return [
            'Unreachable'                => Enrollee::UNREACHABLE,
            'Consented'                  => Enrollee::CONSENTED,
            'To Call'                    => Enrollee::TO_CALL,
            'Marked for Auto-Enrollment' => Enrollee::QUEUE_AUTO_ENROLLMENT,
            'Non responsive'             => Enrollee::NON_RESPONSIVE,
        ];
    }
}
