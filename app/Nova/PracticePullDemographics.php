<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\SharedModels\Entities\PracticePull\Demographics;
use App\Nova\Actions\PracticePull\ImportDemographics;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;

class PracticePullDemographics extends Resource
{
    public static $group = CpmConstants::NOVA_GROUP_PRACTICE_DATA_PULLS;
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = Demographics::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'mrn',
        'first_name',
        'last_name',
        'dob',
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
        return [new ImportDemographics()];
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
            Text::make('MRN', 'mrn')->sortable(),
            Text::make('First Name', 'first_name')->sortable(),
            Text::make('Last Name', 'last_name')->sortable(),
            Date::make('Last Encounter', 'last_encounter')->sortable(),
            Date::make('DOB', 'dob')->sortable(),
            Text::make('Gender', 'gender')->sortable(),
            Text::make('Lang', 'lang')->sortable(),
            Text::make('Provider Name', 'referring_provider_name')->sortable(),
            Text::make('Cell Phone', 'cell_phone')->sortable(),
            Text::make('Home Phone', 'home_phone')->sortable(),
            Text::make('Other Phone', 'other_phone')->sortable(),
            Text::make('Primary Phone', 'primary_phone')->sortable(),
            Text::make('Email', 'email')->sortable(),
            Text::make('Address', 'street')->sortable(),
            Text::make('Address 2', 'street2')->sortable(),
            Text::make('City', 'city')->sortable(),
            Text::make('State', 'state')->sortable(),
            Text::make('Zip', 'zip')->sortable(),
            Text::make('Primary Insurance', 'primary_insurance')->sortable(),
            Text::make('Secondary Insurance', 'secondary_insurance')->sortable(),
            Text::make('Tertiary Insurance', 'tertiary_insurance')->sortable(),
            ID::make()->sortable(),
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

    public static function label()
    {
        return 'Demographics';
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
