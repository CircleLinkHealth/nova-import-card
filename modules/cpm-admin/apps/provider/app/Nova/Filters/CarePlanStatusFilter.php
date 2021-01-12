<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Filters;

use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class CarePlanStatusFilter extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    public $name = 'CarePlan Status';

    /**
     * @var string
     */
    private $default;

    /**
     * CarePlanStatusFilter constructor.
     */
    public function __construct(string $default)
    {
        $this->default = $default;
    }

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
        return $query
            ->whereHas('patient.carePlan', function ($q) use ($value) {
                $q->whereStatus($value);
            })
            ->whereHas('patient.patientInfo', function ($q) {
                $q->whereCcmStatus(Patient::ENROLLED);
            });
    }

    public function default()
    {
        return $this->default;
    }

    /**
     * Get the filter's available options.
     *
     * @return array
     */
    public function options(Request $request)
    {
        return [
            'Draft'             => CarePlan::DRAFT,
            'QA Approved'       => CarePlan::QA_APPROVED,
            'RN Approved'       => CarePlan::RN_APPROVED,
            'Provider Approved' => CarePlan::PROVIDER_APPROVED,
        ];
    }
}
