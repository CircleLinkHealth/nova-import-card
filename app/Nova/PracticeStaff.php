<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\Nova\Importers\PracticeStaff as PracticeStaffImporter;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Sparclex\NovaImportCard\NovaImportCard;

class PracticeStaff extends Resource
{
    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = \App\Constants::NOVA_GROUP_PRACTICES;

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
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }

    /**
     * Get the cards available for the request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function cards(Request $request)
    {
        return [new NovaImportCard(self::class)];
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),

            //user
            Text::make('First Name'),

            //user
            Text::make('Last Name'),

            //suffix - users
            Text::make('Clinical Level'),

            //users
            Text::make('Email'),

            //            //practice role users
            //            Text::make('Role'),
            //
            HasOne::make('providerInfo'),
            //provider info - look into this
            Boolean::make('Can Approve All Care Plans', 'providerInfo.approve_own_care_plans'),

            //            HasMany::make('phoneNumbers'),
            //
            //                        //phone numbers
            //            Number::make('Phone', 'phoneNumbers.number'),
            //                        //phone numbers
            //            Number::make('Phone Extension', 'phoneNumbers.extension'),
            //
            //                        //phone numbers
            //            Text::make('Phone Type', 'phoneNumbers.type'),

            //            //emr_direct_addresses
            //            Text::make('EMR Direct Address'),
            //
            //            //location user
            //            Text::make('Locations')
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param \Illuminate\Http\Request $request
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
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @param \Illuminate\Database\Eloquent\Builder   $query
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
     * @param \Illuminate\Http\Request $request
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
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @param \Illuminate\Database\Eloquent\Builder   $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function relatableQuery(NovaRequest $request, $query)
    {
        return $query->practiceStaff();
    }
}
