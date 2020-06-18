<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Filters\CcdaView;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class ValidationErrorsCcdaFilter extends Filter
{
    const STATUS_DOESNT_HAVE_ERRORS = 'doesnthave';
    const STATUS_HAS_ERRORS         = 'has';
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';
    public $name      = 'Validation Errors';

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
        return $query->when(self::STATUS_HAS_ERRORS === $value, function ($q) {
            $q->whereNotNull('validation_errors');
        })->when(self::STATUS_DOESNT_HAVE_ERRORS === $value, function ($q) {
            $q->whereNull('validation_errors');
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
            'Has Errors'           => self::STATUS_HAS_ERRORS,
            'Does Not Have Errors' => self::STATUS_DOESNT_HAVE_ERRORS,
        ];
    }
}
