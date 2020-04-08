<?php

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class UserRoleFilter extends Filter
{
    /**
     * The displayable name of the filter.
     *
     * @var string
     */
    public $name = 'Role';

    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    /**
     * Apply the filter to the given query.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $value
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        return $query->ofType($value);
    }

    /**
     * Get the filter's available options.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function options(Request $request)
    {
        return [
            'CLH Super Admin'             => 'administrator',
            'Participant'                 => 'participant',
            'API CCD Vendor'              => 'api-ccd-vendor',
            'API Data Consumer'           => 'api-data-consumer',
            'Viewer'                      => 'viewer',
            'Office Admin'                => 'office_admin',
            'Non CCM Care Center'         => 'no-ccm-care-center',
            'No Access'                   => 'no-access',
            'Administrator - View Only'   => 'administrator-view-only',
            'Program Lead'                => 'practice-lead',
            'Registered Nurse'            => 'registered-nurse',
            'Specialist'                  => 'specialist',
            'Salesperson'                 => 'salesperson',
            'Care Ambassador'             => 'care-ambassador',
            'Care Ambassador - View Only' => 'care-ambassador-view-only',
            'Medical Assistant'           => 'med_assistant',
            'Provider'                    => 'provider',
            'CLH Care Coach'              => 'care-center',
            'Care Coach'                  => 'care-center-external',
            'EHR Report Writer'           => 'ehr-report-writer',
            'SAAS Admin'                  => 'saas-admin',
            'Saas Admin - View Only'      => 'saas-admin-view-only',
            'CCM Admin'                   => 'software-only',
            'CLH Developer'               => 'developer',
        ];
    }
}
