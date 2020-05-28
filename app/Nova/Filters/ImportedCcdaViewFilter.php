<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class ImportedCcdaViewFilter extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';
    public $name      = 'Imported';

    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  mixed                                 $imported
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $imported)
    {
        return $query->when(true === $imported, function ($q) {
            $q->whereNotNull('patient_user_id');
        })->when(false === $imported, function ($q) {
            $q->whereNull('patient_user_id');
        });
    }

    /**
     * Get the filter's available options.
     *
     * @return array
     */
    public function options(Request $request)
    {
        return [
            'Imported'     => true,
            'Not Imported' => false,
        ];
    }
}
