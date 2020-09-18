<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Filters;

use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class PatientAutoEnrollmentStatus extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'boolean-filter';

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
        if (isset($value['uninvited']) && true === $value['uninvited']) {
            return $query->whereNotIn('status', [Enrollee::QUEUE_AUTO_ENROLLMENT, Enrollee::CONSENTED])
                ->where('auto_enrollment_triggered', false);
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
            'Uninvited' => 'uninvited',
        ];
    }
}
