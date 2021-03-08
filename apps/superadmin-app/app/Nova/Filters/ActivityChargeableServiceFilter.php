<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Filters;

use CircleLinkHealth\Customer\Entities\ChargeableService;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class ActivityChargeableServiceFilter extends Filter
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
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  mixed                                 $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        if ('n/a' === $value) {
            return $query->whereNull('chargeable_service_id');
        }

        /** @var ChargeableService $cs */
        $cs = ChargeableService::cached()->firstWhere('code', '=', $value);

        if ( ! $cs) {
            throw new \Exception("could not find chargeable service $value");
        }

        return $query->where('chargeable_service_id', '=', $cs->id);
    }

    /**
     * Get the filter's available options.
     *
     * @return array
     */
    public function options(Request $request)
    {
        return [
            'N/A'            => 'n/a',
            'CCM'            => ChargeableService::CCM,
            'CCM (RHC/FQHC)' => ChargeableService::GENERAL_CARE_MANAGEMENT,
            'BHI'            => ChargeableService::BHI,
            'PCM'            => ChargeableService::PCM,
            'RPM'            => ChargeableService::RPM,
        ];
    }
}
