<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\Nova\Importers\PracticeStaff as PracticeStaffImporter;
use App\User;
use Circlelinkhealth\ClhImportCardExtended\ClhImportCardExtended;
use Illuminate\Http\Request;
use Jubeki\Nova\Cards\Linkable\LinkableAway;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class PracticeStaff extends Resource
{
    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = \CircleLinkHealth\Customer\CpmConstants::NOVA_GROUP_PRACTICES;

    public static $importer = PracticeStaffImporter::class;
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = User::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'first_name',
        'last_name',
        'email',
        'suffix',
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
        return [];
    }

    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    public function authorizedToDelete(Request $request)
    {
        return false;
    }

    public function authorizedToForceDelete(Request $request)
    {
        return false;
    }

    public function authorizedToUpdate(Request $request)
    {
        return true;
    }

    /**
     * Get the cards available for the request.
     *
     * @return array
     */
    public function cards(Request $request)
    {
        return [
            ClhImportCardExtended::forUser(auth()->user(), self::class),
            (new LinkableAway())
                ->title('Create Practice Staff CSV Template')
                ->url('https://drive.google.com/file/d/1rdjIAgSKbsyjCc_oLg0j3ISsywAUMA1t/view?usp=sharing')
                ->subtitle('Click to download.')
                ->target('_self'),
        ];
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),

            Text::make('First Name')->sortable(),

            Text::make('Last Name')->sortable(),

            Text::make('Clinical Level', 'suffix')->sortable(),

            Text::make('Email')->sortable(),

            HasOne::make('providerInfo'),

            Boolean::make('Can Approve All Care Plans', 'providerInfo.approve_own_care_plans'),
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
     * Build an "index" query for the given resource.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->practiceStaff();
    }

    public static function label()
    {
        return 'Staff Members';
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
        return $query->practiceStaff();
    }
}
