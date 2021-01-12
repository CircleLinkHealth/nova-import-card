<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class UserRoleFilter extends Filter
{
    public const ROLES_MAP = [
        'administrator'             => 'CLH Super Admin',
        'participant'               => 'Participant',
        'api-ccd-vendor'            => 'API CCD Vendor',
        'api-data-consumer'         => 'API Data Consumer',
        'aprima-api-location'       => 'API Data Consumer and CCD Vendor',
        'viewer'                    => 'Viewer',
        'office_admin'              => 'Office Admin',
        'no-ccm-care-center'        => 'Non CCM Care Center',
        'no-access'                 => 'No Access',
        'administrator-view-only'   => 'Administrator - View Only',
        'practice-lead'             => 'Program Lead',
        'registered-nurse'          => 'Registered Nurse',
        'specialist'                => 'Specialist',
        'salesperson'               => 'Salesperson',
        'care-ambassador'           => 'Care Ambassador',
        'care-ambassador-view-only' => 'Care Ambassador - View Only',
        'med_assistant'             => 'Medical Assistant',
        'provider'                  => 'Provider',
        'care-center'               => 'CLH Care Coach',
        'care-center-external'      => 'Care Coach',
        'ehr-report-writer'         => 'EHR Report Writer',
        'saas-admin'                => 'SAAS Admin',
        'saas-admin-view-only'      => 'Saas Admin - View Only',
        'software-only'             => 'CCM Admin',
        'developer'                 => 'CLH Developer',
    ];

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
    public $name = 'Role';

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
        return $query->ofType($value);
    }

    /**
     * Get the filter's available options.
     *
     * @return array
     */
    public function options(Request $request)
    {
        $keys   = array_keys(self::ROLES_MAP);
        $values = array_values(self::ROLES_MAP);

        $result = [];
        $len    = sizeof($keys);
        for ($i = 0; $i < $len; ++$i) {
            $result[$values[$i]] = $keys[$i];
        }

        return $result;
    }
}
