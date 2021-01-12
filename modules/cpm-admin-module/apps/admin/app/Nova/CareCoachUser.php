<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\Nova\User as NovaUser;
use Laravel\Nova\Http\Requests\NovaRequest;

class CareCoachUser extends NovaUser
{
    /**
     * Indicates if the resource should be displayed in the sidebar.
     *
     * @var bool
     */
    public static $displayInNavigation = true;

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = \CircleLinkHealth\Customer\CpmConstants::NOVA_GROUP_CARE_COACHES;

    /**
     * Build an "index" query for the given resource.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->careCoaches();
    }

    public static function label()
    {
        return 'Users';
    }

    /**
     * Build a "relatable" query for the given resource.
     *
     * This query determines which instances of the model may be attached to other resources.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function relatableQuery(NovaRequest $request, $query)
    {
        return $query->careCoaches();
    }
}
