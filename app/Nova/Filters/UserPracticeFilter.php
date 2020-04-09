<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Filters;

use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class UserPracticeFilter extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';
    /**
     * The displayable name of the filter.
     *
     * @var string
     */
    public $name = 'Practice';

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
        return $query->where('program_id', '=', $value);
    }

    /**
     * Get the filter's available options.
     *
     * @return array
     */
    public function options(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();
        if ($user->isAdmin()) {
            $collection = Practice::all('id', 'display_name');
        } else {
            $viewableProgramIds = $user->viewableProgramIds();

            $collection = Practice::whereIn('id', $viewableProgramIds)
                ->get(['id', 'display_name']);
        }

        return $collection
            ->mapWithKeys(function ($item) {
                return [$item->display_name => $item->id];
            })
            ->toArray();
    }
}
