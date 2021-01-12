<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Filters;

use CircleLinkHealth\Customer\Entities\Patient;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class UnreachablePatientsFilter extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';
    private $practiceId;

    /**
     * UnreachablePatientsFilter constructor.
     *
     * @param $practiceId
     */
    public function __construct($practiceId)
    {
        $this->practiceId = $practiceId;
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
        return $query->with('patientInfo')
            ->whereDoesntHave('enrollmentInvitationLinks')
            ->where('program_id', '=', $value)
            ->whereHas('patientInfo', function ($patient) {
                $patient->where('ccm_status', '=', Patient::UNREACHABLE);
            });
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
            'ccm_status' => Patient::UNREACHABLE,
        ];
    }
}
