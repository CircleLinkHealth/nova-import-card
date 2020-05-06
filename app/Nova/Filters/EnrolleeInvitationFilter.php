<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Filters;

use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class EnrolleeInvitationFilter extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';
    public $practiceId;

    /**
     * EnrolleeInvitationFilter constructor.
     * @param $practiceId
     */
    public function __construct($practiceId)
    {
        $this->practiceId = $practiceId;
    }

    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  mixed                                 $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        return $query->whereIn('status', [
            Enrollee::TO_CALL,
            Enrollee::UNREACHABLE,
        ])->where('practice_id', $value)
            ->where('user_id', '=', null);
    }

    public function default()
    {
        return $this->practiceId;
    }

    /**
     * Get the filter's available options.
     *
     * @return array
     */
    public function options(Request $request)
    {
        return [
            'On Call queue' => Enrollee::TO_CALL,
            'Unreachable'   => Enrollee::UNREACHABLE, // get these to value
        ];
    }
}
