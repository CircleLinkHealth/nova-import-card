<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Filters\CcdaView;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class ImportedCcdaViewFilter extends Filter
{
    const STATUS_IMPORTED     = 'imported';
    const STATUS_NOT_IMPORTED = 'not_imported';
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
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed                                 $status
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $status)
    {
        return $query->when(self::STATUS_IMPORTED === $status, function ($q) {
            $q->whereNotNull('patient_user_id');
        })->when(self::STATUS_NOT_IMPORTED === $status, function ($q) {
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
            'Imported'     => self::STATUS_IMPORTED,
            'Not Imported' => self::STATUS_NOT_IMPORTED,
        ];
    }
}
