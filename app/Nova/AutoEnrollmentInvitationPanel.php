<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\Constants;
use App\Nova\Actions\EnrolleesInvitationAction;
use App\Nova\Actions\PatientsInvitationAction;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;

class AutoEnrollmentInvitationPanel extends Resource
{
    public static $group = Constants::NOVA_GROUP_ENROLLMENT;
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \CircleLinkHealth\Customer\Entities\Practice::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'name', 'display_name',
    ];

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * Get the actions available for the resource.
     *
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            (new EnrolleesInvitationAction())->canSee(function () {
                return true;
            })->canRun(function () {
                return true;
            }),
            //            new PatientsInvitationAction(),
        ];
    }

    /**
     * @return bool
     */
    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    /**
     * @return bool
     */
    public function authorizedToDelete(Request $request)
    {
        return false;
    }

    /**
     * @return bool
     */
    public function authorizedToUpdate(Request $request)
    {
        return false;
    }

    /**
     * Get the cards available for the request.
     *
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()
                ->hideFromIndex()
                ->hideFromDetail()
                ->hideWhenCreating()
                ->hideWhenUpdating()
                ->sortable(),

            Text::make('Name', 'display_name')
                ->hideWhenCreating()
                ->sortable()
                ->readonly(true),
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * @return string
     */
    public static function label()
    {
        return 'Invitations Panel';
    }

    /**
     * Get the lenses available for the resource.
     *
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }
}
